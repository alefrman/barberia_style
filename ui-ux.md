# Barbería Style — Diseño UI/UX (ui-ux.md)

Documentación del sistema de diseño, estructura visual y decisiones de experiencia de usuario.

---

## 1. Paleta de colores

Paleta definida en `tailwind.config` (layouts público y admin) y referencias CSS.

| Token | Hex | Uso |
|---|---|---|
| `gold` | `#F59E0B` | Color primario / acentos / botones principales |
| `goldlight` | `#FBBF24` | Hover / texto destacado |
| `golddark` | `#B45309` | Sombra / variante oscura |
| `cream` | `#FEF9C3` | Texto secundario / rótulos |
| `dark` | `#2D3748` | Fondo de paneles / formularios |
| `darksoft` | `#1F2937` | Sidebar / secciones alternas |
| `darkdeep` | `#111827` | Fondo base de página |

**Convención**: las secciones alternan `darkdeep` y `darksoft` (a veces con `border-y border-white/5`) para crear ritmo visual.

---

## 2. Tipografía

- **Display (títulos)**: `Playfair Display` (serif), clase `font-display`. Grandes tamaños `text-4xl` a `text-7xl`.
- **Cuerpo**: `Inter` (sans). Texto base `text-sm`/`text-base`.
- **Rótulos**: uso intensivo de `uppercase tracking-[.25em]` + `text-[10px]`/`text-[11px]` para etiquetas estilo "eyebrow".
- Carga vía Google Fonts en ambos layouts.

---

## 3. Iconografía

- **Font Awesome 6.5.2** (CDN) en todo el sitio y panel.
- Iconos temáticos: `fa-scissors` (servicios/cortes), `fa-images` (galería), `fa-boxes-stacked` (inventario), `fa-money-bill-transfer` (gastos), `fa-user-tie` (barberos), `fa-calendar-check` (citas), `fa-user-shield` (usuarios).

---

## 4. Componentes CSS propios (`public/assets/css/style.css`)

| Clase | Función |
|---|---|
| `.eyebrow` | Rótulo superior de sección (mayúsculas, espaciado amplio, color dorado). |
| `.text-gold-grad` | Texto con degradado dorado. |
| `.navbar.scrolled` | Navbar fija que cambia de apariencia al hacer scroll. |
| `.btn-shine` | Efecto de brillo que recorre el botón al pasar el cursor. |
| `.hero-bg` | Fondo del hero con capa semitransparente. |
| `.marquee` / `.marquee-track` | Carrusel infinito animado (30s, loop). |
| `.reveal` / `.reveal.is-visible` | Animación de aparición al hacer scroll (IntersectionObserver, delays por índice). |
| `.gallery-item` | Tarjeta de galería con zoom de imagen en hover. |
| `.gallery-overlay` | Overlay con degradado que revela título/descripción al pasar el cursor. |

---

## 5. Sitio público — UX

### Navegación
- Navbar fija superior (`fixed`) con blur de fondo (`backdrop-blur-xl`), logo + marca, enlaces (Inicio, Servicios, Productos, Equipo, Galería) y botón **Panel** que abre `/admin.php`.
- Al hacer scroll la navbar marca estado `.scrolled` (sombra/borde).
- **Menú móvil**: botón hamburguesa que despliega panel lateral con las mismas opciones.

### Secciones del home (orden)
1. Hero (titular, CTAs, foto).
2. Marquee animado.
3. Servicios.
4. Productos (preview 4).
5. Equipo.
6. Galería (preview 4).
7. Estadísticas (condicional).
8. CTA de reserva.

### Patrones
- **Reveal on scroll**: casi todo aparece con animación escalonada (`--delay` calculado por índice dentro de cada grid).
- **Empty states amigables**: "Próximamente nuestros servicios.", "Aún no hay fotos en la galería.", etc.
- **Tarjetas** con hover: zoom de imagen, borde dorado, overlay con texto.
- **Lightbox (GLightbox)**: en la galería, al hacer clic en una foto se abre un visor con título, descripción, zoom y navegación. Configuración en `public/assets/js/gallery.js`.

---

## 6. Panel de administración — UX

### Layout
- **Sidebar fija** (desktop, `lg:flex`): agrupa navegación en "Gestión" (Dashboard, Citas, Inventario, Servicios, Barberos, Gastos, Galería) y "Sistema" (Usuarios *solo Superadmin*, Ver sitio web, Cerrar sesión).
- **Barra superior**: título de la página + nombre/rol del usuario + avatar.
- **Menú móvil**: panel deslizante con las mismas opciones (botón hamburguesa en la barra superior).
- Contenido en `main` con `lg:pl-64` (margen por la sidebar) y contenedor `max-w-7xl`.

### Elementos de UI
- **Mensajes flash**: alerta superior (verde éxito / rojo error) con icono.
- **Tablas**: filas con `hover:bg-gold/5`, cabeceras en `uppercase tracking-widest`, miniaturas de imagen a la izquierda de la fila.
- **Tarjetas de resumen** (dashboard, inventario, gastos): icono + número grande + etiqueta.
- **Formularios**: inputs oscuros (`bg-dark/60`), borde sutil, focus dorado con anillo (`focus:border-gold/60 focus:ring-2 focus:ring-gold/20`), placeholder claro.
- **Validación en vivo de agenda (citas)**: al cambiar fecha/hora/barbero/servicio se consulta la disponibilidad por AJAX (debounce 400 ms); los barberos con choque se marcan con borde y mensaje **rojo** (`border-red-500` + texto `text-red-400`) bajo su select, con botón **"Usar HH:MM"** que aplica la hora sugerida, y un **banner superior rojo** resume los conflictos con "(disponible a partir de las HH:MM)"; el botón de guardar queda bloqueado hasta resolverlos.
- **Botón principal**: dorado (`bg-gold text-darkdeep`) con efecto `btn-shine`; botones secundarios con borde.
- **Acciones por fila**: iconos de editar (lápiz) y eliminar (papelera) con `confirm` de JavaScript. En inventario, además un botón **reloj** abre el historial de stock del producto.
- **Historial de inventario**: página con tarjetas del producto (stock actual, ganancia pendiente, precio, costo), formulario **Reponer stock** (cantidad + nota) y tabla de movimientos con píldoras por tipo (creación dorado, reposición verde, ajuste azul) y cantidades en verde/rojo según entrada/salida.
- **Upload de imagen** con preview en vivo (FileReader → `#preview-img`).

### Gráficas del dashboard (Chart.js)
- Tema **oscuro** alineado a la paleta: fondos `#1F2937` (tooltips), ejes `rgba(255,255,255,0.08)`, ticks y leyenda en `rgba(254,249,195,0.6)` (cream).
- Semántica de color: **verde** = ingresos/ganancia, **rojo** = gastos/salidas, **dorado** = KPI principal (ganancia neta).
- Donas: paleta rotativa (`gold`, `goldlight`, rojo, azul, verde, morado, rosa, gris) con `cutout: 62%`.
- Tarjetas KPI financieros con borde de color en hover según su naturaleza (verde/rojo/dorado/azul).
- Toggle **Mes/Semana** en la gráfica de estados: botón activo dorado (`bg-gold text-darkdeep`), inactivo oscuro con borde.
- **Top productos más vendidos**: **barras horizontales** (`indexAxis: 'y'`) para que los nombres de producto se vean **completos** (`autoSkip: false` + `afterFit` con ancho mínimo del eje). Plugin inline `endLabels` dibuja el **valor al final de cada barra** (unidades doradas), y la tarjeta muestra un **badge con el total de unidades vendidas**.

### Flujo de eliminar
- Confirmación nativa del navegador.
- Si hay dependencia (FK), mensaje flash amigable: *"No se puede eliminar: el producto está asociado a una o más citas."*

---

## 7. Accesibilidad y calidad

- Etiquetas `label` con `for` en formularios.
- `aria-label` en botones de icono puro (menú móvil).
- `alt` descriptivo en imágenes (con fallback genérico en galería).
- Textos con contraste sobre fondos oscuros (blancos/crema sobre `darkdeep`).
- Animaciones suaves (transiciones de hover, reveal) sin sobrecarga.

---

## 8. Responsive (breakpoints)

| Breakpoint | Comportamiento |
|---|---|
| Móvil | Grids de 1 columna (home) o 2 (galería), menú hamburguesa, botones a ancho completo. |
| `sm` (640px) | Grids de 2 columnas, navbar muestra botón Panel. |
| `lg` (1024px) | Grids de 3-4 columnas, sidebar del admin visible, navbar con todos los enlaces. |
