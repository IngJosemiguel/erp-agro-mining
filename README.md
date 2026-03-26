<p align="center">
  <img src="public/img/logo.png" alt="ERP AgroMine" width="120">
</p>

<h1 align="center">ERP AgroMine</h1>

<p align="center">
  <strong>Sistema de Gestión Empresarial para Agroindustria y Minería</strong><br>
  Facturación Electrónica SUNAT · POS Offline-First · Contabilidad Automatizada · Inventario Inteligente
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-11-red?logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Livewire-3-purple?logo=livewire&logoColor=white" alt="Livewire 3">
  <img src="https://img.shields.io/badge/MySQL-8.0+-orange?logo=mysql&logoColor=white" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/JavaScript-ES6+-yellow?logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/SUNAT-UBL%202.1-green" alt="SUNAT UBL 2.1">
  <img src="https://img.shields.io/badge/Licencia-Propietaria-important" alt="Licencia">
</p>

---

## Problema que Resuelve

Las empresas agroindustriales y mineras en Perú enfrentan:

- **Facturación manual y errores tributarios** ante SUNAT, con boletas y facturas que deben cumplir el estándar UBL 2.1
- **Control de inventario deficiente** sin trazabilidad por lotes, series ni ubicaciones de almacén
- **Contabilidad desconectada** que requiere doble digitación entre ventas/compras y el libro contable
- **Operaciones remotas sin internet** donde los puntos de venta fallan al perder conexión
- **Gestión financiera fragmentada** en múltiples hojas de cálculo sin visibilidad gerencial en tiempo real

**ERP AgroMine** es una solución integral, 100% web, que unifica ventas, compras, inventario, contabilidad y finanzas en un solo sistema con facturación electrónica SUNAT, POS offline-first y automatización contable de doble partida.

---

## Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND                                  │
│  Blade Templates + Alpine.js + Chart.js + Lucide Icons      │
│  Dark Mode Premium · Responsive · PWA-ready                 │
├─────────────────────────────────────────────────────────────┤
│                    LIVEWIRE 3 (SPA-like)                     │
│  52 componentes reactivos · Real-time updates · WebSocket   │
├─────────────────────────────────────────────────────────────┤
│                    LARAVEL 11 (Backend)                      │
│  13 Controllers · 6 Services · 38 Models · 19 Migrations    │
│  API RESTful v1 · Middleware Auth · Trait Multi-Empresa      │
├─────────────────────────────────────────────────────────────┤
│                    INTEGRACIONES                             │
│  SUNAT API (OSE) · APIs.net.pe (DNI/RUC) · IA Predictiva   │
├─────────────────────────────────────────────────────────────┤
│                    BASE DE DATOS                             │
│  MySQL 8.0+ · 35+ tablas · Índices optimizados              │
│  Soporte multi-empresa · Multi-sucursal · Multi-almacén     │
└─────────────────────────────────────────────────────────────┘
```

---

## Stack Tecnológico

| Capa | Tecnología | Versión | Propósito |
|------|-----------|---------|-----------|
| **Backend** | PHP | 8.2+ | Lenguaje servidor |
| **Framework** | Laravel | 11.x | MVC, ORM, Queues, Auth |
| **Reactivo** | Livewire | 3.x | Componentes SPA-like sin JS |
| **Frontend** | Blade + Alpine.js | 3.x | Templates + interactividad |
| **CSS** | Custom Design System | - | Dark mode premium, variables CSS |
| **Gráficos** | Chart.js | 4.x | Dashboards y reportes visuales |
| **Iconos** | Lucide | 0.3x | Iconografía vectorial |
| **Base de datos** | MySQL / MariaDB | 8.0+ / 10.6+ | Almacenamiento relacional |
| **SUNAT** | UBL 2.1 / REST | - | Facturación electrónica Perú |
| **Servidor** | Apache (XAMPP) | 2.4+ | Servidor HTTP |
| **PDF** | DomPDF | - | Generación de comprobantes PDF |

---

## Módulos del Sistema

### 1. Dashboard Gerencial

Centro de inteligencia de negocio con datos en tiempo real.

| Submódulo | Descripción |
|-----------|-------------|
| KPIs Operativos | Ventas hoy, mes, productos, comprobantes SUNAT |
| KPIs Financieros | Utilidad del mes, margen %, compras, gastos, CxC, CxP |
| Gráfico Ventas 7 días | Línea con gradiente, tendencia diaria |
| Gráfico Flujo 6 meses | Barras comparativas Ingresos vs Egresos |
| Próximos Cobros/Pagos | Alertas de vencimiento CxC y CxP |
| Top Clientes | Ranking mensual por facturación |
| Alertas Stock Bajo | Productos en riesgo de agotamiento |
| Accesos Rápidos | Links a todas las secciones críticas |

### 2. Ventas

Gestión completa del ciclo de ventas con facturación electrónica.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| Atención al Cliente | `AtencionCliente.php` | Punto de venta con búsqueda de productos, IGV, descuentos |
| Listado de Ventas | `VentasListado.php` | Historial filtrable con estados y acciones |
| Notas de Crédito/Débito | `NotasCredito.php` | Emisión de NC/ND electrónicas |
| Cotizaciones | `CotizacionesListado.php` | Gestión de cotizaciones a clientes |
| Despachos | `DespachosListado.php` | Control de entregas y logística |
| CPE Pendientes | `CpePendientes.php` | Monitor de comprobantes no aceptados por SUNAT |
| Comprobantes No Enviados | `ComprobantesNoEnviados.php` | Reenvío masivo a SUNAT |
| Resúmenes Diarios | `ResumenesDiarios.php` | Generación de resúmenes para boletas |
| PDF de Comprobantes | `VentaPdfController.php` | Generación de PDF A4 y ticket |

### 3. POS (Punto de Venta)

Sistema offline-first con sincronización automática.

| Submódulo | Descripción |
|-----------|-------------|
| Punto de Venta | Interfaz de caja con Alpine.js, funciona sin internet |
| Caja Chica | `CajaChicaListado.php` — Ingresos y egresos de caja |
| Sincronización | API RESTful con cola de sincronización |
| Cálculo automático IGV | 18% con desglose en tiempo real |
| Múltiples formas de pago | Contado, crédito, transferencia |

### 4. Productos y Servicios

Catálogo empresarial con trazabilidad completa.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| Catálogo | `ProductosTable.php` | CRUD con stock, precios, imágenes |
| Categorías | `CategoriasTable.php` | Organización jerárquica |
| Marcas | `MarcasTable.php` | Gestión de marcas comerciales |
| Packs / Combos | `PacksListado.php` | Productos compuestos |
| Series | `SeriesProducto.php` | Trazabilidad por serie unitaria |
| Códigos de Barra | `CodigosBarraListado.php` | Generación y lectura |
| Ubicaciones Almacén | `UbicacionesAlmacen.php` | Localización física de productos |
| Laboratorios | `LaboratoriosListado.php` | Laboratorios (sector farmacéutico) |
| Tipos de Existencia | `TiposExistenciaTable.php` | Clasificación SUNAT |

### 5. Clientes

Gestión 360° de la cartera de clientes.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| Clientes | `ClientesTable.php` | CRUD con DNI/RUC, créditos |
| Tipos de Cliente | `TiposClienteTable.php` | Clasificación por tipo |
| Zonas | Vista blade | Segmentación geográfica |
| Consulta DNI/RUC | `ConsultaDocumentoService.php` | API externa apis.net.pe |

### 6. Proveedores

Catálogo de proveedores.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| Proveedores | `ProveedoresTable.php` | CRUD con RUC y condiciones |
| Cotización a Proveedor | `CotizacionProveedor.php` | Solicitud de precios |

### 7. Compras

Ciclo completo de adquisiciones.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| Nueva Compra | `ComprasNuevo.php` | Registro con IGV, forma de pago |
| Listado Compras | `ComprasListado.php` | Historial con filtros y anulación |
| Órdenes de Compra | `OrdenesCompraListado.php` | OC formales a proveedores |
| Gastos Operativos | `GastosListado.php` | Control de gastos por categoría |
| Activos Fijos | `ActivosFijos.php` | Registro y depreciación |
| Créditos Bancarios | `CreditosBancarios.php` | Préstamos y financiamiento |

### 8. Inventario

Control total con Kardex, lotes y predicción IA.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| Movimientos | `MovimientosInventario.php` | Entradas, salidas, ajustes |
| Traslados | `TrasladosInventario.php` | Entre almacenes/sucursales |
| Devolución a Proveedor | `DevolucionProveedorComponent.php` | Devoluciones formalizadas |
| Kardex | `KardexReporte.php` | Kardex por producto con saldos |
| Kardex Valorizado | `KardexValorizado.php` | Valorización con métodos de costeo |
| Reporte Stock | `ReporteInventario.php` | Stock global multi-almacén |
| Formato 13.1 SUNAT | `Formato13Sunat.php` | Registro de inventario permanente |
| Predicción IA | `PrediccionStock.php` | Algoritmo predictivo de demanda |

### 9. Comprobantes Avanzados

Documentos tributarios especializados.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| Retenciones | `RetencionesListado.php` | CRE SUNAT |
| Percepciones | `PercepcionesListado.php` | CPE SUNAT |
| Liquidaciones de Compra | `LiquidacionesListado.php` | Para proveedores informales |
| Órdenes de Pedido | `OrdenesPedido.php` | Pre-ventas y reservas |
| Guías de Remisión | `GuiasRemision.php` | GRE electrónica SUNAT |

### 10. Finanzas y Contabilidad

Módulo financiero profesional con contabilidad automatizada.

| Submódulo | Componente | Descripción |
|-----------|-----------|-------------|
| **Tesorería** | | |
| Cuentas por Cobrar | `CuentasCobrar.php` | CxC con cobros parciales y alertas |
| Cuentas por Pagar | `CuentasPagar.php` | CxP con pagos y vencimientos |
| Caja Chica | `CajaChicaListado.php` | Ingresos/egresos diarios |
| Créditos Bancarios | `CreditosBancarios.php` | Préstamos bancarios |
| Activos Fijos | `ActivosFijos.php` | Patrimonio y depreciación |
| **Contabilidad** | | |
| Plan de Cuentas | `PlanCuentas.php` | PCGE Perú personalizable |
| Asientos Contables | `AsientosContables.php` | Libro de asientos con partida doble |
| Libro Diario | `LibroDiario.php` | Registro cronológico |
| Libro Mayor | `LibroMayor.php` | Cuenta T por período |
| Balance General | `BalanceGeneral.php` | Activo = Pasivo + Patrimonio |
| Estado de Resultados | `EstadoResultados.php` | Pérdidas y ganancias |
| Centros de Costo | `CentrosCosto.php` | Distribución por área |
| Presupuesto | `Presupuesto.php` | Planeación vs ejecución |
| **Automatización** | | |
| Asientos Automáticos | `ContabilidadAutomaticaService.php` | Genera partida doble automática |

---

## Motor de Contabilidad Automatizada

El sistema genera **asientos contables automáticos** con partida doble para cada transacción:

| Evento | Debe | Haber |
|--------|------|-------|
| Venta al contado | 10.01 Caja | 70.01 Ventas + 40.11 IGV |
| Venta a crédito | 12.01 CxC | 70.01 Ventas + 40.11 IGV |
| Cobro de CxC | 10.01 Caja | 12.01 CxC |
| Compra | 60.01 Compras + IGV | 42.01 CxP / 10.01 Caja |
| Pago de CxP | 42.01 CxP | 10.01 Caja |
| Gasto operativo | 63.xx Categoría | 10.01 Caja |
| Anulación | Reversa espejo | Invierte Debe ↔ Haber |

---

## API RESTful

Endpoints disponibles en `/api/v1/`:

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/pos/sync-ventas` | Sincronización POS offline |
| `GET` | `/productos` | Catálogo de productos |
| `GET` | `/clientes` | Listado de clientes |
| `GET` | `/clientes/buscar` | Búsqueda por DNI/RUC |
| `POST` | `/ventas` | Crear venta |
| `POST` | `/facturacion/emitir` | Emitir CPE a SUNAT |
| `POST` | `/guias/emitir` | Emitir GRE a SUNAT |
| `GET` | `/inventario/stock` | Consulta de stock |
| `GET` | `/consulta-documento/{tipo}/{numero}` | Consulta DNI/RUC vía APIs.net.pe |

---

## Integraciones Externas

| Servicio | Uso | Estado |
|----------|-----|--------|
| **SUNAT OSE** | Emisión de facturas, boletas, NC, ND, GRE electrónicas | Producción |
| **APIs.net.pe** | Consulta de DNI (RENIEC) y RUC (SUNAT) en tiempo real | Producción |
| **Predicción IA** | Algoritmo heurístico de predicción de demanda de stock | Producción |
| **Chart.js** | Gráficos interactivos para dashboard y reportes | Producción |

---

## Características Técnicas

### Seguridad

- Autenticación con sesiones Laravel (bcrypt)
- Middleware `auth` en todas las rutas protegidas
- CSRF protection en todos los formularios
- Escape automático XSS en templates Blade
- API con autenticación Bearer token

### Multi-Tenancy

- Arquitectura **multi-empresa** con `empresa_id` en todas las tablas
- Soporte **multi-sucursal** y **multi-almacén**
- Trait `TieneContextoEmpresa` para resolución automática del contexto
- Aislamiento completo de datos entre empresas

### Rendimiento

- Índices compuestos optimizados en tablas de alta consulta
- Eager loading en relaciones Eloquent
- Paginación server-side en todos los listados
- Sin N+1 queries (verificado)

### Escalabilidad

- Base de datos normalizada (3NF) con 35+ tablas
- Servicios desacoplados (Service Pattern)
- Componentes Livewire independientes (52 componentes)
- API versionada para integraciones futuras

---

## Estructura del Proyecto

```
erp-agro-mining/
├── app/
│   ├── Http/Controllers/        # 13 controladores (Web + API)
│   │   ├── Api/V1/              # API RESTful versionada
│   │   └── Auth/                # Autenticación
│   ├── Livewire/                # 52 componentes reactivos
│   │   ├── Finanzas/            # 10 componentes financieros
│   │   └── Ventas/              # Atención al cliente
│   ├── Models/                  # 38 modelos Eloquent
│   ├── Services/                # 6 servicios de negocio
│   │   └── Sunat/               # Facturación electrónica
│   ├── Traits/                  # Traits reutilizables
│   └── Exceptions/              # Excepciones personalizadas
├── database/
│   └── migrations/              # 19 migraciones
├── resources/
│   └── views/
│       ├── layouts/             # Layout principal dark-mode
│       ├── livewire/            # Vistas Livewire
│       └── modules/             # Vistas por módulo
├── routes/
│   ├── web.php                  # Rutas web (155 líneas)
│   └── api.php                  # Rutas API v1
├── config/                      # Configuración Laravel
└── public/                      # Assets públicos
```

---

## Requisitos del Sistema

| Requerimiento | Mínimo | Recomendado |
|---------------|--------|-------------|
| PHP | 8.2 | 8.3 |
| MySQL | 8.0 | 8.0+ |
| Memoria RAM | 2 GB | 4 GB |
| Extensiones PHP | `soap`, `gd`, `openssl`, `mbstring`, `pdo_mysql` | + `redis` |
| Servidor Web | Apache 2.4 | Nginx 1.24 |
| Node.js | No requerido | Opcional (para assets) |

---

## Instalación

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-org/erp-agro-mining.git
cd erp-agro-mining

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_DATABASE=erp_agromine
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Ejecutar migraciones
php artisan migrate

# 6. Crear usuario administrador
php artisan tinker
# > User::create(['name'=>'Admin', 'email'=>'admin@empresa.pe', 'password'=>bcrypt('secret'), 'empresa_id'=>1]);

# 7. Levantar servidor
php artisan serve
```

---

## Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Archivos PHP + Blade | **260+** |
| Componentes Livewire | **52** |
| Modelos Eloquent | **38** |
| Controladores | **13** |
| Servicios de Negocio | **6** |
| Migraciones | **19** |
| Tablas en BD | **35+** |
| Rutas Web | **70+** |
| Rutas API | **15+** |
| Líneas de código (estimado) | **25,000+** |

---

## Roadmap

- [x] Facturación electrónica SUNAT (UBL 2.1)
- [x] POS offline-first con sincronización
- [x] Inventario con Kardex y predicción IA
- [x] Contabilidad automatizada (partida doble)
- [x] Dashboard Gerencial BI
- [x] CxC / CxP con alertas de vencimiento
- [x] Multi-empresa / multi-sucursal / multi-almacén
- [x] Guías de Remisión Electrónica (GRE)
- [x] Retenciones, Percepciones, Liquidaciones
- [ ] Módulo RRHH (planillas, asistencia)
- [ ] App móvil (React Native / Flutter)
- [ ] Reportes exportables (Excel/PDF avanzado)
- [ ] Integración con bancos (Estado de cuenta)

---

## Licencia

Este software es **propietario**. Todos los derechos reservados.
La reproducción, distribución o uso no autorizado está prohibido sin el consentimiento explícito del titular.

---

<p align="center">
  <strong>ERP AgroMine</strong> — Gestión Empresarial Inteligente para Perú<br>
  Desarrollado con Laravel 11 + Livewire 3 + MySQL
</p>
