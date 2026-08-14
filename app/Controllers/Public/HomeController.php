<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;
use App\Models\Product;
use App\Models\Team;
use App\Models\Gallery;

/**
 * HomeController
 *
 * Controlador de la vista pública principal.
 * Todo el contenido se extrae de la base de datos.
 */
class HomeController extends Controller
{
    /**
     * Página de inicio: presenta servicios, productos destacados, equipo y galería.
     */
    public function index(Request $request, array $params): Response
    {
        $services  = $this->orderedServices();
        $products  = $this->orderedProducts();
        $team      = Team::where(['is_active' => 1]);
        $gallery   = $this->orderedGallery();

        return $this->view('public/home/index', [
            'services' => $services,
            'products' => $products,
            'team'     => $team,
            'gallery'  => $gallery,
            'stats'    => [
                'services' => Service::count(['is_active' => 1]),
                'products' => Product::count(['is_active' => 1]),
                'barbers'  => Team::count(['is_active' => 1]),
                'styles'   => Gallery::count(['is_active' => 1]),
            ],
            'title' => 'Cortes y estética masculina',
            'active' => 'home',
        ]);
    }

    /**
     * Página de servicios.
     */
    public function services(Request $request, array $params): Response
    {
        $services = $this->orderedServices();

        return $this->view('public/home/services', [
            'services' => $services,
            'title'    => 'Servicios',
            'active'   => 'services',
        ]);
    }

    /**
     * Página de productos.
     */
    public function products(Request $request, array $params): Response
    {
        $products = $this->orderedProducts();

        return $this->view('public/home/products', [
            'products' => $products,
            'title'    => 'Productos',
            'active'   => 'products',
        ]);
    }

    /**
     * Página del equipo.
     */
    public function team(Request $request, array $params): Response
    {
        $team = Team::where(['is_active' => 1]);
        usort(
            $team,
            fn($a, $b) => (int) $a->getAttribute('sort_order') <=> (int) $b->getAttribute('sort_order')
        );

        return $this->view('public/home/team', [
            'team'  => $team,
            'title' => 'Nuestro Equipo',
            'active' => 'team',
        ]);
    }

    /**
     * Página de galería.
     */
    public function gallery(Request $request, array $params): Response
    {
        $gallery = $this->orderedGallery();

        return $this->view('public/home/gallery', [
            'gallery' => $gallery,
            'title'   => 'Galería',
            'active'  => 'gallery',
        ]);
    }

    /**
     * Servicios activos ordenados por orden definido en el panel.
     */
    private function orderedServices(): array
    {
        $services = Service::where(['is_active' => 1]);
        usort(
            $services,
            fn($a, $b) => (int) $a->getAttribute('sort_order') <=> (int) $b->getAttribute('sort_order')
        );
        return $services;
    }

    /**
     * Productos activos ordenados por orden definido en el panel.
     */
    private function orderedProducts(): array
    {
        $products = Product::where(['is_active' => 1]);
        usort(
            $products,
            fn($a, $b) => (int) $a->getAttribute('sort_order') <=> (int) $b->getAttribute('sort_order')
        );
        return $products;
    }

    /**
     * Fotos activas ordenadas por orden definido en el panel.
     */
    private function orderedGallery(): array
    {
        $gallery = Gallery::where(['is_active' => 1]);
        usort(
            $gallery,
            fn($a, $b) => (int) $a->getAttribute('sort_order') <=> (int) $b->getAttribute('sort_order')
        );
        return $gallery;
    }
}
