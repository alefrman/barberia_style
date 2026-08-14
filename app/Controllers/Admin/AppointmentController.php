<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Settings;
use App\Models\Appointment;
use App\Models\AppointmentProduct;
use App\Models\AppointmentService;
use App\Models\AppointmentStatus;
use App\Models\AppointmentType;
use App\Models\Product;
use App\Models\Service;
use App\Models\Team;
use App\Models\User;

/**
 * AppointmentController
 *
 * Módulo de Citas (turnos): cabecera + servicios + productos,
 * control de stock y totales calculados desde la base de datos.
 */
class AppointmentController extends Controller
{
    /**
     * Listado con filtros por estado, fecha y búsqueda.
     */
    public function index(Request $request, array $params): Response
    {
        $statusId = (int) $request->input('status_id', 0);
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));
        $q = trim((string) $request->input('q', ''));

        $where = [];
        $bind = [];

        if ($statusId > 0) {
            $where[] = 'a.status_id = :status_id';
            $bind['status_id'] = $statusId;
        }
        if ($dateFrom !== '') {
            $where[] = 'a.appointment_date >= :date_from';
            $bind['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[] = 'a.appointment_date <= :date_to';
            $bind['date_to'] = $dateTo;
        }
        if ($q !== '') {
            $where[] = '(a.client_name LIKE :q OR a.client_phone LIKE :q OR a.client_email LIKE :q)';
            $bind['q'] = '%' . $q . '%';
        }

        $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT a.*, t.name AS type_name, s.name AS status_name,
                       (SELECT COUNT(*) FROM appointment_services asv WHERE asv.appointment_id = a.id) AS services_count,
                       (SELECT COUNT(*) FROM appointment_products apd WHERE apd.appointment_id = a.id) AS products_count
                FROM appointments a
                INNER JOIN appointment_types t ON t.id = a.type_id
                INNER JOIN appointment_statuses s ON s.id = a.status_id
                {$whereSql}
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
                LIMIT 500";

        $rows = Database::fetchAll($sql, $bind);

        // Contadores por estado para las tarjetas resumen
        $counts = ['today' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'total' => 0];
        foreach (Database::fetchAll("SELECT COUNT(*) AS c, status_id FROM appointments GROUP BY status_id") as $r) {
            $name = strtolower((string) AppointmentStatus::find((int) $r['status_id'])?->getAttribute('name') ?? '');
            if ($name === 'pendiente') $counts['pending'] = (int) $r['c'];
            if ($name === 'confirmada') $counts['confirmed'] = (int) $r['c'];
            if ($name === 'completada') $counts['completed'] = (int) $r['c'];
        }
        $counts['today'] = (int) Database::fetchValue("SELECT COUNT(*) FROM appointments WHERE appointment_date = :d", ['d' => date('Y-m-d')]);
        $counts['total'] = (int) Database::fetchValue("SELECT COUNT(*) FROM appointments");

        $statuses = AppointmentStatus::all('id', 'ASC');

        return $this->view('admin/appointments/index', [
            'title'    => 'Citas',
            'user'     => Auth::user(),
            'active'   => 'appointments',
            'rows'     => $rows,
            'counts'   => $counts,
            'statuses' => $statuses,
            'filters'  => ['status_id' => $statusId, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'q' => $q],
        ], 'admin');
    }

    /**
     * Formulario de nueva cita.
     */
    public function create(Request $request, array $params): Response
    {
        return $this->formView(null, 'Nueva cita');
    }

    /**
     * Guarda una cita nueva (con detalles y control de stock).
     */
    public function store(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/appointments');
        }

        $data = $this->extractAppointment($request);
        $errors = $this->validateAppointment($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/appointments/create');
        }        Database::beginTransaction();
        try {
            $appointment = Appointment::create([
                'type_id'    => $data['type_id'],
                'status_id'  => $data['status_id'],
                'client_name' => $data['client_name'],
                'client_phone' => $data['client_phone'],
                'client_email' => $data['client_email'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'notes'      => $data['notes'],
                'subtotal'   => 0,
                'total'      => 0,
                'created_by' => Auth::id(),
            ]);

            $id = (int) $appointment->getAttribute('id');
            [$subtotal] = $this->saveDetails($id, $data['services'], $data['products']);

            Appointment::updateWhere(['id' => $id], ['subtotal' => $subtotal, 'total' => $subtotal]);

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'No se pudo guardar la cita: ' . $e->getMessage());
            return $this->redirect('/admin.php/appointments/create');
        }

        Session::flash('success', 'Cita creada correctamente.');
        return $this->redirect('/admin.php/appointments');
    }

    /**
     * Detalle de una cita.
     */
    public function show(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $row = $this->appointmentRow($id);

        if ($row === null) {
            Session::flash('error', 'Cita no encontrada.');
            return $this->redirect('/admin.php/appointments');
        }

        $services = Database::fetchAll(
            "SELECT asv.*, s.name AS service_name, t.name AS barber_name
             FROM appointment_services asv
             LEFT JOIN services s ON s.id = asv.service_id
             LEFT JOIN team t ON t.id = asv.barber_id
             WHERE asv.appointment_id = :id ORDER BY asv.id",
            ['id' => $id]
        );

        $products = Database::fetchAll(
            "SELECT ap.*, p.name AS product_name
             FROM appointment_products ap
             LEFT JOIN products p ON p.id = ap.product_id
             WHERE ap.appointment_id = :id ORDER BY ap.id",
            ['id' => $id]
        );

        return $this->view('admin/appointments/show', [
            'title'    => 'Detalle de cita',
            'user'     => Auth::user(),
            'active'   => 'appointments',
            'row'      => $row,
            'services' => $services,
            'products' => $products,
            'creator'  => $row['created_by'] ? User::find((int) $row['created_by'])?->getAttribute('name') : null,
        ], 'admin');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $appointment = Appointment::find($id);

        if ($appointment === null) {
            Session::flash('error', 'Cita no encontrada.');
            return $this->redirect('/admin.php/appointments');
        }

        return $this->formView($appointment, 'Editar cita');
    }

    /**
     * Actualiza una cita (recalcula totales y ajusta stock).
     */
    public function update(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/appointments');
        }

        $id = (int) ($params['id'] ?? 0);
        $appointment = Appointment::find($id);

        if ($appointment === null) {
            Session::flash('error', 'Cita no encontrada.');
            return $this->redirect('/admin.php/appointments');
        }

        $data = $this->extractAppointment($request);
        $errors = $this->validateAppointment($data, $id);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/appointments/edit/' . $id);
        }

        Database::beginTransaction();
        try {
            // Restaurar stock de los productos previos
            $this->adjustStock($id, true);

            // Eliminar detalles previos (se vuelven a insertar)
            Database::execute("DELETE FROM appointment_products WHERE appointment_id = :id", ['id' => $id]);
            Database::execute("DELETE FROM appointment_services WHERE appointment_id = :id", ['id' => $id]);

            Appointment::updateWhere(['id' => $id], [
                'type_id'    => $data['type_id'],
                'status_id'  => $data['status_id'],
                'client_name' => $data['client_name'],
                'client_phone' => $data['client_phone'],
                'client_email' => $data['client_email'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'notes'      => $data['notes'],
            ]);

            [$subtotal] = $this->saveDetails($id, $data['services'], $data['products']);

            Appointment::updateWhere(['id' => $id], ['subtotal' => $subtotal, 'total' => $subtotal]);

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'No se pudo actualizar la cita: ' . $e->getMessage());
            return $this->redirect('/admin.php/appointments/edit/' . $id);
        }

        Session::flash('success', 'Cita actualizada correctamente.');
        return $this->redirect('/admin.php/appointments');
    }

    /**
     * Elimina una cita (restaura stock).
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/appointments');
        }

        $id = (int) ($params['id'] ?? 0);
        $appointment = Appointment::find($id);

        if ($appointment === null) {
            Session::flash('error', 'Cita no encontrada.');
            return $this->redirect('/admin.php/appointments');
        }

        Database::beginTransaction();
        try {
            $this->adjustStock($id, true);
            $appointment->delete(); // ON DELETE CASCADE limpia detalles
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'No se pudo eliminar la cita: ' . $e->getMessage());
            return $this->redirect('/admin.php/appointments');
        }

        Session::flash('success', 'Cita eliminada correctamente.');
        return $this->redirect('/admin.php/appointments');
    }

    /* ============================================================
     * HELPERS
     * ========================================================== */

    /**
     * Renderiza el formulario de cita (crear/editar).
     */
    private function formView(?Appointment $appointment, string $title): Response
    {
        $servicesList = Service::all('sort_order', 'ASC');
        $productsList = Product::all('sort_order', 'ASC');
        $barbers = Team::where(['is_active' => 1]);
        $types = AppointmentType::all('id', 'ASC');
        $statuses = AppointmentStatus::all('id', 'ASC');

        $values = $appointment !== null ? $appointment->toArray() : [
            'type_id' => 1, 'status_id' => 1, 'client_name' => '', 'client_phone' => '',
            'client_email' => '', 'appointment_date' => date('Y-m-d'),
            'appointment_time' => date('H:i', strtotime('+1 hour')), 'notes' => '',
        ];

        $selectedServices = [];
        $selectedProducts = [];
        if ($appointment !== null) {
            $id = (int) $appointment->getAttribute('id');
            foreach (Database::fetchAll("SELECT * FROM appointment_services WHERE appointment_id = :id", ['id' => $id]) as $r) {
                $selectedServices[] = $r;
            }
            foreach (Database::fetchAll("SELECT * FROM appointment_products WHERE appointment_id = :id", ['id' => $id]) as $r) {
                $selectedProducts[] = $r;
            }
        }

        return $this->view('admin/appointments/form', [
            'title'      => $title,
            'user'       => Auth::user(),
            'active'     => 'appointments',
            'editing'    => $appointment,
            'values'     => $values,
            'services'   => $servicesList,
            'products'   => $productsList,
            'barbers'    => $barbers,
            'types'      => $types,
            'statuses'   => $statuses,
            'hours'      => Settings::businessHours(),
            'selectedServices' => $selectedServices,
            'selectedProducts' => $selectedProducts,
        ], 'admin');
    }

    /**
     * Extrae y normaliza los campos de la petición.
     */
    private function extractAppointment(Request $request): array
    {
        return [
            'type_id'    => (int) $request->input('type_id', 0),
            'status_id'  => (int) $request->input('status_id', 0),
            'client_name' => trim((string) $request->input('client_name', '')),
            'client_phone' => trim((string) $request->input('client_phone', '')),
            'client_email' => trim((string) $request->input('client_email', '')),
            'appointment_date' => trim((string) $request->input('appointment_date', '')),
            'appointment_time' => trim((string) $request->input('appointment_time', '')),
            'notes'      => trim((string) $request->input('notes', '')),
            'services'   => $this->pairs('service_id', 'barber_id'),
            'products'   => $this->pairs('product_id', 'quantity'),
        ];
    }

    /**
     * Lee arreglos paralelos del POST y los empareja por índice.
     */
    private function pairs(string $key, string $secondKey): array
    {
        $ids = (array) ($_POST[$key] ?? []);
        $second = (array) ($_POST[$secondKey] ?? []);

        $result = [];
        foreach ($ids as $index => $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }
            $result[] = [
                'id' => $id,
                $secondKey => (int) ($second[$index] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Validación de la cita.
     * $appointmentId: id de la cita al editar (para restar el stock ya descontado por esa cita).
     */
    private function validateAppointment(array $data, ?int $appointmentId = null): array
    {
        $errors = [];

        if (AppointmentType::find($data['type_id']) === null) {
            $errors[] = 'Selecciona un tipo de cita válido.';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['appointment_date'])) {
            $typeName = strtolower((string) AppointmentType::find($data['type_id'])->getAttribute('name'));
            $isToday = $data['appointment_date'] === date('Y-m-d');
            if ($isToday && $typeName === 'programada') {
                $errors[] = 'Una cita para hoy debe tener el tipo "Ahora".';
            } elseif (!$isToday && $typeName === 'ahora') {
                $errors[] = 'Una cita para una fecha futura debe tener el tipo "Programada".';
            }
        }
        if (AppointmentStatus::find($data['status_id']) === null) {
            $errors[] = 'Selecciona un estado válido.';
        }
        if ($data['client_name'] === '') {
            $errors[] = 'El nombre del cliente es obligatorio.';
        } elseif (mb_strlen($data['client_name']) > 150) {
            $errors[] = 'El nombre del cliente no puede superar 150 caracteres.';
        }
        if ($data['client_phone'] !== '' && !preg_match('/^\+503 \d{4}-\d{4}$/', $data['client_phone'])) {
            $errors[] = 'El teléfono debe tener el formato +503 0000-0000.';
        }
        if ($data['client_email'] !== '' && !filter_var($data['client_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un email de cliente válido.';
        }
        if ($data['appointment_date'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['appointment_date'])) {
            $errors[] = 'Ingresa una fecha de cita válida.';
        } elseif (strcmp($data['appointment_date'], date('Y-m-d')) < 0) {
            $errors[] = 'No puedes registrar citas en fechas pasadas.';
        }
        if ($data['appointment_time'] === '' || !preg_match('/^\d{2}:\d{2}/', $data['appointment_time'])) {
            $errors[] = 'Ingresa una hora de cita válida.';
        } elseif ($data['appointment_date'] === date('Y-m-d') && $data['appointment_time'] < date('H:i', strtotime('+1 hour'))) {
            $errors[] = 'Para citas de hoy, la hora debe ser al menos 1 hora después de la hora actual.';
        }

        // Restricción por horario de atención (días cerrados y rango de horas)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['appointment_date']) && preg_match('/^\d{2}:\d{2}/', $data['appointment_time'])) {
            $dayKey = ['mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday', 'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday', 'sun' => 'sunday'][strtolower(date('D', strtotime($data['appointment_date'])))] ?? '';
            $dayLabels = ['monday' => 'lunes', 'tuesday' => 'martes', 'wednesday' => 'miércoles', 'thursday' => 'jueves', 'friday' => 'viernes', 'saturday' => 'sábado', 'sunday' => 'domingo'];

            if ($dayKey !== '') {
                $hours = Settings::businessHours();
                $range = $hours[$dayKey] ?? ['open' => '', 'close' => ''];
                $open = (string) ($range['open'] ?? '');
                $close = (string) ($range['close'] ?? '');

                if ($open === '' || $close === '') {
                    $errors[] = 'El negocio está cerrado los ' . $dayLabels[$dayKey] . '. Selecciona otro día.';
                } elseif ($data['appointment_time'] < $open || $data['appointment_time'] >= $close) {
                    $errors[] = 'La hora de cita debe estar dentro del horario de atención (' . $open . ' a ' . $close . ').';
                }
            }
        }
        if ($data['services'] === [] && $data['products'] === []) {
            $errors[] = 'Debes agregar al menos un servicio o un producto a la cita.';
        }

        foreach ($data['products'] as $productRow) {
            $product = Product::find($productRow['id']);
            if ($product === null) {
                continue;
            }
            $name = (string) $product->getAttribute('name');
            $stock = (int) $product->getAttribute('stock');

            $previousQty = 0;
            if ($appointmentId !== null) {
                $previousQty = (int) (Database::fetchValue(
                    "SELECT COALESCE(SUM(quantity), 0) FROM appointment_products WHERE appointment_id = :aid AND product_id = :pid",
                    ['aid' => $appointmentId, 'pid' => $productRow['id']]
                ) ?? 0);
            }
            $available = $stock + $previousQty;

            if ($available <= 0) {
                $errors[] = 'El producto "' . $name . '" está agotado y no se puede agregar a la cita.';
            } elseif ($productRow['quantity'] > $available) {
                $errors[] = 'Solo hay ' . $available . ' unidades disponibles de "' . $name . '".';
            }
        }

        return $errors;
    }

    /**
     * Inserta los detalles (servicios y productos) y descuenta stock.
     * Retorna [subtotal, productsSubtotal].
     */
    private function saveDetails(int $appointmentId, array $services, array $products): array
    {
        $servicesTotal = 0.0;
        foreach ($services as $row) {
            $service = Service::find($row['id']);
            if ($service === null) {
                continue;
            }
            $price = (float) $service->getAttribute('price');
            $servicesTotal += $price;
            AppointmentService::create([
                'appointment_id' => $appointmentId,
                'service_id'     => $row['id'],
                'barber_id'      => $row['barber_id'] > 0 ? $row['barber_id'] : null,
                'price'          => $price,
            ]);
        }

        $productsTotal = 0.0;
        foreach ($products as $row) {
            $product = Product::find($row['id']);
            if ($product === null) {
                continue;
            }
            $quantity = max(1, min($row['quantity'], 99));
            $price = (float) $product->getAttribute('price');

            $stock = (int) $product->getAttribute('stock');
            $finalQty = $quantity;
            if ($stock - $quantity < 0) {
                $finalQty = max(0, $stock);
            }
            if ($finalQty <= 0) {
                continue;
            }

            $productsTotal += $price * $finalQty;

            AppointmentProduct::create([
                'appointment_id' => $appointmentId,
                'product_id'     => $row['id'],
                'quantity'       => $finalQty,
                'price'          => $price,
            ]);

            Product::updateWhere(['id' => $row['id']], ['stock' => $stock - $finalQty]);
        }

        $subtotal = $servicesTotal + $productsTotal;
        return [$subtotal, $productsTotal];
    }

    /**
     * Ajusta el stock de los productos de una cita.
     * $restore = true  -> devuelve stock (al eliminar/editar)
     * $restore = false -> descuenta stock (al crear)
     * (La edición restaura primero y vuelve a descontar con saveDetails).
     */
    private function adjustStock(int $appointmentId, bool $restore): void
    {
        $rows = Database::fetchAll(
            "SELECT product_id, quantity FROM appointment_products WHERE appointment_id = :id",
            ['id' => $appointmentId]
        );

        foreach ($rows as $row) {
            $product = Product::find((int) $row['product_id']);
            if ($product === null) {
                continue;
            }
            $stock = (int) $product->getAttribute('stock');
            $qty = (int) $row['quantity'];
            Product::updateWhere(
                ['id' => (int) $row['product_id']],
                ['stock' => $restore ? $stock + $qty : $stock - $qty]
            );
        }
    }

    /**
     * Fila de cita con nombres de tipo y estado.
     */
    private function appointmentRow(int $id): ?array
    {
        return Database::fetch(
            "SELECT a.*, t.name AS type_name, s.name AS status_name
             FROM appointments a
             INNER JOIN appointment_types t ON t.id = a.type_id
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE a.id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * Verifica el token CSRF.
     */
    private function validCsrf(Request $request): bool
    {
        $token = $request->input('_csrf');
        if (Session::verifyCsrf(is_string($token) ? $token : null)) {
            return true;
        }
        Session::flash('error', 'Token de seguridad inválido.');
        return false;
    }
}
