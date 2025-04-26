<template>
    <main class="main">
      <div class="panel-header ">
        
          <div class="d-flex justify-content-between align-items-center py-3">
            <h4 >
              <i class="bi bi-file-earmark-text mr-2"></i> Cotizaciones
            </h4>
          </div>
      
      </div>
  
      <!-- Contenedor principal -->
  
            <!-- Listado de cotizaciones -->
            <template v-if="listado == 1">
              <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <Button
                    @click="mostrarDetalle('venta', 'cotizacion')" 
          label="Nueva cotización"
          icon="pi pi-plus"
          class="p-button-secondary"
        />
                  
                  </div>
                  <div class="col-md-5">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text bg-white">
                          <i class="bi bi-search text-primary"></i>
                        </span>
                      </div>
                      <input 
                        type="text" 
                        v-model="buscar" 
                        @keyup="listarCotizacion(1, buscar)"
                        class="form-control" 
                        placeholder="Buscar por nombre, documento o fecha..."
                      >
                    </div>
                  </div>
                </div>
  
                <div class="table-responsive">
                  <table class="table table-hover table-bordered">
                    <thead class="bg-light">
                      <tr>
                        <th class="border-top-0">ID</th>
                        <th class="border-top-0">Fecha</th>
                        <th class="border-top-0">Cliente</th>
                        <th class="border-top-0">Documento</th>
                        <th class="border-top-0">Total</th>
                        <th class="border-top-0">Validez</th>
                        <th class="border-top-0">Estado</th>
                        <th class="border-top-0 text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="venta in arrayVenta" :key="venta.id">
                        <td class="align-middle"><span class="badge badge-dark">#{{ venta.id }}</span></td>
                        <td class="align-middle">
                          <div>{{ new Intl.DateTimeFormat('es-ES').format(new Date(venta.fecha_hora)) }}</div>
                          <small class="text-muted">{{ new Date(venta.fecha_hora).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}) }}</small>
                        </td>
                        <td class="align-middle">{{ venta.nombre }}</td>
                        <td class="align-middle">{{ venta.num_documento }}</td>
                        <td class="align-middle font-weight-bold badge-dark">{{ venta.total }}</td>
                        <td class="align-middle">
                          <span 
                            :class="[
                              'badge',
                              Math.floor((new Date(venta.validez) - new Date()) / (1000 * 60 * 60 * 24)) + 1 <= 0 
                                ? 'badge-danger' 
                                : Math.floor((new Date(venta.validez) - new Date()) / (1000 * 60 * 60 * 24)) + 1 <= 3 
                                  ? 'badge-warning' 
                                  : 'badge-success'
                            ]"
                          >
                            {{ Math.floor((new Date(venta.validez) - new Date()) / (1000 * 60 * 60 * 24)) + 1 }} días
                          </span>
                        </td>
                        <td class="align-middle">
                          <span class="badge" :class="[venta.condicion ? 'badge-success' : 'badge-danger']">
                            {{ venta.condicion ? 'Activa' : 'Inactiva' }}
                          </span>
                        </td>
                        <td class="align-middle text-center">
  <div class="d-flex gap-2"> <!-- Contenedor flex con separación -->
    <button
      type="button"
      class="btn btn-md btn-secondary"
      title="Ver detalles"
      @click="abrirModalDetalles(venta)"
      style="min-width: 40px;"
    >
      <i class="bi bi-eye"></i>
    </button>
    <button
      type="button"
      class="btn btn-md btn-primary"
      title="Generar PDF"
      @click="pdfVenta(venta.id)"
      style="min-width: 40px;"
    >
      <i class="bi bi-file-pdf"></i>
    </button>
    <button
      type="button"
      class="btn btn-md btn-warning"
      title="Editar"
      @click="mostrarDetalle('venta', 'editar', venta)"
      style="min-width: 40px;"
    >
      <i class="bi bi-pencil"></i>
    </button>
    <button
      v-if="venta.condicion"
      type="button"
      class="btn btn-md btn-danger"
      title="Desactivar"
      @click="desactivarCotizacion(venta.id)"
      style="min-width: 40px;"
    >
      <i class="bi bi-trash"></i>
    </button>
    <button
      v-else
      type="button"
      class="btn btn-md btn-success"
      title="Activar"
      @click="activarCotizacion(venta.id)"
      style="min-width: 40px;"
    >
      <i class="bi bi-check-lg"></i>
    </button>
  </div>
</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
  
                <!-- Paginación mejorada -->
                <nav v-if="pagination.last_page > 1" aria-label="Navegación de páginas" class="mt-3">
                  <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                      <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1, buscar)">
                        <i class="bi bi-chevron-left"></i>
                      </a>
                    </li>
                    <li 
                      v-for="page in pagesNumber" 
                      :key="page" 
                      class="page-item" 
                      :class="{ active: page === pagination.current_page }"
                    >
                      <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar)" v-text="page"></a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                      <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1, buscar)">
                        <i class="bi bi-chevron-right"></i>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </template>
  
            <!-- Formulario de cotización -->
            <template v-else-if="listado == 0">
              <div class="p-3">
                <h5 class="border-bottom pb-2 mb-4">
                  {{ idcotizacionv ? 'Editar cotización' : 'Nueva cotización' }}
                </h5>
                
                <!-- Información principal -->
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="font-weight-bold text-dark">Cliente <span class="text-danger">*</span></label>
                      <v-select 
                        :on-search="selectCliente" 
                        label="num_documento" 
                        :options="arrayCliente"
                        placeholder="Buscar por documento..." 
                        :onChange="getDatosCliente"
                        v-model="clienteSeleccionado"
                        class="style-chooser"
                      >
                      </v-select>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label class="text-muted">Documento</label>
                      <input type="text" class="form-control bg-light" v-model="nitcliente" readonly>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="font-weight-bold text-dark">Almacén <span class="text-danger">*</span></label>
                      <select class="form-control" v-model="AlmacenSeleccionado" @change="getDatosAlmacen">
                        <option value="0" disabled>Seleccione</option>
                        <option v-for="opcion in arrayAlmacenes" :key="opcion.id" :value="opcion.id">
                          {{ opcion.nombre_almacen }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="font-weight-bold text-dark">Días de validez <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" v-model="dias_validez">
                    </div>
                  </div>
                </div>
  
                <!-- Separador con título -->
                <div class="section-divider mt-3 mb-4">
                  <span>Agregar artículos</span>
                </div>
                
                <!-- Buscador de artículos -->
                <div class="row mb-4">
                  <div class="col-md-6">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <button @click="abrirModal()" class="btn btn-primary">
                          <i class="bi bi-search mr-1"></i> Buscar
                        </button>
                      </div>
                      <input 
                        type="text" 
                        class="form-control" 
                        v-model="codigo" 
                        ref="articuloRef"
                        @keyup="buscarArticulo()" 
                        placeholder="Código del artículo"
                      >
                    </div>
                    <div v-if="nombre_articulo" class="mt-2">
                      <span class="badge badge-info py-2 px-3">{{ nombre_articulo }}</span>
                    </div>
                  </div>
                </div>
                
                <!-- Detalles del artículo -->
                <div class="row mt-3" v-if="arrayArticuloSeleccionado.length > 0">
                  <div class="col-12">
                    <div class="card bg-light border">
                      <div class="card-body py-3">
                        <div class="row align-items-center">
                          <!-- Imagen -->
                          <div class="col-md-2 text-center">
                            <img 
                              v-if="arrayArticuloSeleccionado[0].fotografia"
                              :src="'img/articulo/' + arrayArticuloSeleccionado[0].fotografia + '?t=' + new Date().getTime()"
                              class="img-fluid rounded border" 
                              style="max-height: 80px;"
                              alt="Imagen del artículo"
                            />
                            <img 
                              v-else
                              src="https://cdn-icons-png.freepik.com/512/14/14795.png"
                              class="img-fluid rounded border" 
                              style="max-height: 80px;"
                              alt="Imagen genérica"
                            >
                          </div>
                          
                          <!-- Stock -->
                          <div class="col-md-2">
                            <label class="d-block small text-muted mb-1">Stock disponible</label>
                            <div class="input-group input-group-sm">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-box"></i></span>
                              </div>
                              <input 
                                type="text" 
                                class="form-control text-center font-weight-bold" 
                                :class="[parseFloat(saldo_stock) > 10 ? 'text-success' : 'text-danger']"
                                v-model="saldo_stock" 
                                readonly
                              >
                            </div>
                          </div>
                          
                          <!-- Precio -->
                          <div class="col-md-3">
                            <label class="d-block small text-muted mb-1">Precio de venta</label>
                            <div class="input-group input-group-sm">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                              </div>
                              <select class="form-control" v-model="precioseleccionado" @change="calcularPrecioTotal">
                                <option :value="precio_venta" v-if="precio_venta">Precio Venta ({{ precio_venta }})</option>
                                <option :value="precio_uno" v-if="arrayPrecios[0] && precio_uno">{{ arrayPrecios[0].nombre_precio }} ({{ precio_uno }})</option>
                                <option :value="precio_dos" v-if="arrayPrecios[1] && precio_dos">{{ arrayPrecios[1].nombre_precio }} ({{ precio_dos }})</option>
                                <option :value="precio_tres" v-if="arrayPrecios[2] && precio_tres">{{ arrayPrecios[2].nombre_precio }} ({{ precio_tres }})</option>
                                <option :value="precio_cuatro" v-if="arrayPrecios[3] && precio_cuatro">{{ arrayPrecios[3].nombre_precio }} ({{ precio_cuatro }})</option>
                              </select>
                            </div>
                          </div>
                          
                          <!-- Cantidad -->
                          <div class="col-md-2">
                            <label class="d-block small text-muted mb-1">Cantidad <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-123"></i></span>
                              </div>
                              <input 
                                type="number" 
                                class="form-control text-center" 
                                v-model="cantidad"
                                @input="calcularPrecioTotal"
                                ref="cantidadRef"
                                min="1"
                              >
                            </div>
                          </div>
                          
                          <!-- Total -->
                          <div class="col-md-2">
                            <label class="d-block small text-muted mb-1">Importe total</label>
                            <div class="input-group input-group-sm">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-cash"></i></span>
                              </div>
                              <input 
                                type="text" 
                                class="form-control text-right font-weight-bold" 
                                v-model="prectotal"
                                readonly
                              >
                            </div>
                          </div>
                          
                          <!-- Botón agregar -->
                          <div class="col-md-1">
                            <label class="d-block small text-muted mb-1">&nbsp;</label>
                            <button 
                              @click="agregarDetalle()" 
                              class="btn btn-success btn-sm btn-block"
                              title="Agregar artículo"
                            >
                              <i class="bi bi-plus-lg"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
  
                <!-- Tabla de artículos agregados -->
                <div class="card mt-3">
                  <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                      <h5 class="card-title mb-0">
                        <i class="bi bi-list-check"></i> Detalle de artículos
                      </h5>
                      <h5 class="card-title text-primary mb-0" v-if="arrayDetalle.length">
                        Total: {{ calcularTotal }}
                      </h5>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-hover mb-0">
                        <thead class="thead-light">
                          <tr>
                            <th class="text-center" style="width: 60px">Acción</th>
                            <th style="width: 100px">Código</th>
                            <th>Artículo</th>
                            <th class="text-right" style="width: 100px">Precio</th>
                            <th class="text-center" style="width: 80px">Unidad</th>
                            <th class="text-center" style="width: 80px">Cantidad</th>
                            <th class="text-right" style="width: 120px">Subtotal</th>
                          </tr>
                        </thead>
                        <tbody v-if="arrayDetalle.length">
                          <tr v-for="(detalle, index) in arrayDetalle" :key="index">
                            <td class="text-center">
                              <button 
                                @click="eliminarDetalle(index)" 
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                title="Eliminar artículo"
                              >
                                <i class="bi bi-trash"></i>
                              </button>
                            </td>
                            <td><span class="badge badge-light">{{ detalle.codigo }}</span></td>
                            <td>{{ detalle.nombre_articulo }}</td>
                            <td class="text-right">{{ detalle.precioseleccionado }}</td>
                            <td class="text-center">{{ detalle.unidad_envase }}</td>
                            <td class="text-center font-weight-bold">{{ detalle.cantidad }}</td>
                            <td class="text-right font-weight-bold">{{ detalle.prectotal }}</td>
                          </tr>
                        </tbody>
                        <tbody v-else>
                          <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                              <i class="bi bi-cart mr-2"></i> No hay artículos agregados a la cotización
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
  
                <!-- Notas y observaciones -->
                <div class="card shadow-sm mt-3 mb-4">
                  <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                      <i class="bi bi-chat-left-text"></i> Observaciones
                    </h5>
                  </div>
                  <div class="card-body">
                    <div class="form-group mb-0">
                      <textarea 
                        class="form-control" 
                        v-model="nota" 
                        rows="2" 
                        placeholder="Agregue observaciones, condiciones especiales o notas para el cliente..."
                      ></textarea>
                    </div>
                  </div>
                </div>
  
                <!-- Botones de acción (ahora al final) -->
                <div class="d-flex justify-content-end mt-4">
                  <button 
                    type="button" 
                    @click="ocultarDetalle()"
                    class="btn btn-secondary mr-2"
                  >
                    <i class="bi bi-x-circle mr-1"></i> Cancelar
                  </button>
                  <button 
                    v-if="idcotizacionv != ''" 
                    type="button" 
                    class="btn btn-warning"
                    @click="editarCotizacion()"
                  >
                    <i class="bi bi-pencil-square mr-1"></i> Actualizar cotización
                  </button>
                  <button 
                    v-else 
                    type="button" 
                    class="btn btn-primary"
                    @click.prevent="registrarCotizacion()"
                  >
                    <i class="bi bi-save mr-1"></i> Guardar cotización
                  </button>
                </div>
              </div>
            </template>
      
      <!-- Modal de selección de artículos -->
      <div 
        class="modal fade" 
        tabindex="-1" 
        :class="{ 'mostrar': modal }" 
        role="dialog" 
        aria-hidden="true"
      >
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-box mr-2"></i>
                {{ tituloModal }}
              </h5>
              <button type="button" class="close" @click="cerrarModal()">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <select class="form-control" v-model="criterioA">
                      <option value="nombre">Nombre</option>
                      <option value="descripcion">Descripción</option>
                      <option value="codigo">Código</option>
                    </select>
                  </div>
                  <input 
                    type="text" 
                    v-model="buscarA" 
                    @keyup="listarArticulo(buscarA, criterioA)"
                    class="form-control" 
                    placeholder="Texto a buscar"
                  >
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead class="thead-light">
                    <tr>
                      <th width="10%">Opciones</th>
                      <th width="15%">Código</th>
                      <th width="35%">Nombre</th>
                      <th width="15%">Categoría</th>
                      <th width="10%">Precio</th>
                      <th width="10%">Stock</th>
                      <th width="5%">Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="articulo in arrayArticulo" :key="articulo.id">
                      <td>
                        <button 
                          type="button" 
                          @click="agregarDetalleModal(articulo)"
                          class="btn btn-success btn-sm"
                        >
                          <i class="bi bi-check-lg"></i>
                        </button>
                      </td>
                      <td v-text="articulo.codigo"></td>
                      <td v-text="articulo.nombre"></td>
                      <td v-text="articulo.nombre_categoria"></td>
                      <td v-text="articulo.precio_venta"></td>
                      <td>
                        <span :class="articulo.saldo_stock > 10 ? 'text-success' : 'text-danger'">
                          {{ articulo.saldo_stock }}
                        </span>
                      </td>
                      <td>
                        <span 
                          :class="[
                            'badge', 
                            articulo.condicion ? 'badge-success' : 'badge-danger'
                          ]"
                        >
                          {{ articulo.condicion ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="cerrarModal()">
                <i class="bi bi-x-circle mr-1"></i> Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Componentes auxiliares -->
      <detallecotizacionventa 
        v-if="showModalDetalle" 
        @cerrar="cerrarModalDetalles"
        @abrirVenta="abrirFormularioCotizacion" 
        :arrayCotizacionSeleccionado="arrayCotizacionSeleccionado"
        :arrayCotizacionVentDet="arrayCotizacionVentDet"
      >
      </detallecotizacionventa>
      
      <registrarventa 
        v-if="showRegistrarVenta" 
        :arrayDetalleCotizacion="arrayDetallesAComprar"
        :arrayCotizacionSeleccionado="arrayCotizacionSeleccionado" 
        @cerrar="cerrarFormularioVenta"
      >
      </registrarventa>
    </main>
  </template>
  
 
  <script>
  import vSelect from 'vue-select';
  import Button from "primevue/button";
export default {
  components: {
    Button,
    vSelect
  },
  
  data() {
    return {
      // Estados de visualización
      listado: 1,
      modal: 0,
      tituloModal: 'Seleccionar artículo',
      showModalDetalle: false,
      showRegistrarVenta: false,
      
      // Datos de cabecera
      titulo: 'Cotización de Venta',
      idcotizacionv: '',
      idcliente: 0,
      clienteSeleccionado: '',
      nitcliente: '',
      telefono: '',
      
      // Almacén
      AlmacenSeleccionado: 1,
      idalmacen: 1,
      arrayAlmacenes: [],
      
      // Artículo seleccionado
      arrayArticuloSeleccionado: [], 
      nombre_articulo: '',
      saldo_stock: '',
      unidad_envase: '',
      cantidad: 1,
      
      // Precios
      precioseleccionado: '',
      prectotal: '',
      arrayPrecios: [],
      precio_uno: '',
      precio_dos: '',
      precio_tres: '',
      precio_cuatro: '',
      precio_venta: '',
      precio: 0,
      
      // Datos cotización
      dias_validez: 7,
      tiempo_entrega: "Inmediata",
      lugar_entrega: "Deposito",
      forma_pago: 'Contado',
      estado_cotizacion: 'En espera',
      titulocard: '',
      nota: "",
      impuesto: 0.18,
      
      // Arrays de datos
      arrayVenta: [],
      arrayCliente: [],
      arrayDetalle: [],
      arrayDetallesAComprar: [],
      arrayCotizacionSeleccionado: {},
      arrayCotizacionVentDet: [],
      
      // Búsqueda y paginación
      buscar: '',
      codigo: '',
      criterioA: 'nombre',
      buscarA: '',
      arrayArticulo: [],
      idarticulo: 0,
      descuento: 0,
      stock: 0,
      
      // Paginación
      pagination: {
        'total': 0,
        'current_page': 0,
        'per_page': 0,
        'last_page': 0,
        'from': 0,
        'to': 0,
      },
      offset: 3,
      criterio: 'nombre',
      
      // Control de errores
      errorVenta: 0,
      errorMostrarMsjVenta: []
    };
  },
  
  watch: {
    cantidad() {
      this.calcularPrecioTotal();
    },
    precioseleccionado() {
      this.calcularPrecioTotal();
    }
  },
  
  computed: {
    // Página actual para paginación
    isActived() {
      return this.pagination.current_page;
    },

    // Calcular páginas para paginación
    pagesNumber() {
      if (!this.pagination.to) {
        return [];
      }

      var from = this.pagination.current_page - this.offset;
      if (from < 1) {
        from = 1;
      }

      var to = from + (this.offset * 2);
      if (to >= this.pagination.last_page) {
        to = this.pagination.last_page;
      }

      var pagesArray = [];
      while (from <= to) {
        pagesArray.push(from);
        from++;
      }
      return pagesArray;
    },

    // Calcular total de la cotización
    calcularTotal() {
      let total = 0;
      this.arrayDetalle.forEach(item => {
        total += parseFloat(item.prectotal || 0);
      });
      return total.toFixed(2);
    }
  },
  
  methods: {
    // LISTADOS
    listarCotizacion(pagina, buscar, criterio = 'nombre') {
      let url = `/cotizacionventa?page=${pagina}&buscar=${buscar}&criterio=${criterio}`;
      
      axios.get(url).then(response => {
        const respuesta = response.data;
        this.arrayVenta = respuesta.cotizacion_venta.data;
        this.pagination = respuesta.pagination;
      }).catch(error => {
        console.error("Error al listar cotizaciones:", error);
      });
    },
    
    cambiarPagina(page, buscar, criterio) {
      this.pagination.current_page = page;
      this.listarCotizacion(page, buscar, criterio);
    },
    
    // SELECCIÓN Y BÚSQUEDA DE CLIENTES
    selectCliente(numero) {
      axios.get(`/cliente/selectClientePorNumero?numero=${numero}`)
        .then(response => {
          this.arrayCliente = response.data.clientes;
        }).catch(error => {
          console.error("Error al buscar cliente:", error);
        });
    },
    
    getDatosCliente(cliente) {
      if (cliente && cliente.id) {
        this.idcliente = cliente.id;
        this.clienteSeleccionado = cliente.nombre;
        this.nitcliente = cliente.num_documento;
        this.telefono = cliente.telefono;
      }
    },
    
    // ALMACÉN
    selectAlmacen() {
      axios.get('/almacen/selectAlmacen')
        .then(response => {
          this.arrayAlmacenes = response.data.almacenes;
        }).catch(error => {
          console.error("Error al cargar almacenes:", error);
        });
    },
    
    getDatosAlmacen() {
      if (this.AlmacenSeleccionado !== '') {
        this.loading = true;
        this.idalmacen = Number(this.AlmacenSeleccionado);
      }
    },
    
    // PRECIOS
    listarPrecio() {
      axios.get('/precios')
        .then(response => {
          this.arrayPrecios = response.data.precio.data;
        }).catch(error => {
          console.error("Error al cargar precios:", error);
        });
    },
    
    mostrarSeleccion() {
      this.calcularPrecioTotal();
    },
    
    // ARTÍCULOS
    buscarArticulo() {
      if (!this.codigo) return;
      
      axios.get(`/articulo/buscarArticuloVenta?filtro=${this.codigo}`)
        .then(response => {
          var respuesta = response.data;
          this.arrayArticulo = respuesta.articulos;
          
          if (this.arrayArticulo.length > 0) {
            const articulo = this.arrayArticulo[0];
            this.articulo = articulo.nombre;
            this.idarticulo = articulo.id;
            this.precio = articulo.precio_venta;
            this.stock = articulo.stock;
            this.agregarDetalleModal(articulo);
          } else {
            this.nombre_articulo = 'No existe este artículo';
            this.idarticulo = 0;
          }
        }).catch(error => {
          console.error("Error al buscar artículo:", error);
        });
    },
    
    listarArticulo(buscar, criterio) {
      axios.get(`/articulo/listarArticuloVenta?buscar=${buscar}&criterio=${criterio}&idAlmacen=${this.idalmacen}`)
        .then(response => {
          var respuesta = response.data;
          this.arrayArticulo = respuesta.articulos;
        }).catch(error => {
          console.error("Error al listar artículos:", error);
        });
    },
    
    agregarDetalleModal(articulo) {
      if (this.encuentra(articulo.id)) {
        swal({
          type: 'error',
          title: 'Error...',
          text: 'Este artículo ya está agregado al detalle'
        });
        return;
      }
      
      // Guardar artículo seleccionado
      this.arrayArticuloSeleccionado = [{
        idarticulo: articulo.id,
        codigo: articulo.codigo,
        saldo_stock: articulo.saldo_stock,
        nombre_articulo: articulo.nombre,
        unidad_envase: articulo.unidad_envase,
        fotografia: articulo.fotografia,
        precio_uno: articulo.precio_uno,
        precio_dos: articulo.precio_dos,
        precio_tres: articulo.precio_tres,
        precio_cuatro: articulo.precio_cuatro,
        precio_venta: articulo.precio_venta
      }];
      
      // Actualizar campos
      this.codigo = this.arrayArticuloSeleccionado[0].codigo;
      this.saldo_stock = this.arrayArticuloSeleccionado[0].saldo_stock;
      this.nombre_articulo = this.arrayArticuloSeleccionado[0].nombre_articulo;
      this.unidad_envase = this.arrayArticuloSeleccionado[0].unidad_envase;
      
      // Guardar precios
      this.precio_uno = this.arrayArticuloSeleccionado[0].precio_uno;
      this.precio_dos = this.arrayArticuloSeleccionado[0].precio_dos;
      this.precio_tres = this.arrayArticuloSeleccionado[0].precio_tres;
      this.precio_cuatro = this.arrayArticuloSeleccionado[0].precio_cuatro;
      this.precio_venta = this.arrayArticuloSeleccionado[0].precio_venta;
      
      // Establecer precio por defecto
      this.precioseleccionado = this.precio_venta || this.precio_uno || 0;
      
      // Inicializar cantidad y calcular total
      this.cantidad = 1;
      this.calcularPrecioTotal();
      
      this.cerrarModal();
    },
    
    // GESTIÓN DE ITEMS EN DETALLE
    encuentra(id) {
      for (var i = 0; i < this.arrayDetalle.length; i++) {
        if (this.arrayDetalle[i].idarticulo == id) {
          return true;
        }
      }
      return false;
    },
    
    eliminarDetalle(index) {
      this.arrayDetalle.splice(index, 1);
    },
    
    agregarDetalle() {
      let me = this;
      
      if (me.arrayArticuloSeleccionado.length == 0 || me.cantidad == 0) {
        console.log("Seleccione un producto o verifique la cantidad");
        return;
      }
      
      if (me.encuentra(me.arrayArticuloSeleccionado[0].idarticulo)) {
        swal({
          type: 'error',
          title: 'Error...',
          text: 'Este artículo ya se encuentra agregado!'
        });
        return;
      }
      
      if (me.cantidad > me.saldo_stock) {
        swal({
          type: 'error',
          title: 'Error...',
          text: 'No hay stock disponible!'
        });
        return;
      }
      
      // Agregar al detalle
      me.arrayDetalle.push({
        idarticulo: me.arrayArticuloSeleccionado[0].idarticulo,
        codigo: me.arrayArticuloSeleccionado[0].codigo,
        nombre_articulo: me.arrayArticuloSeleccionado[0].nombre_articulo,
        precioseleccionado: me.precioseleccionado,
        cantidad: me.cantidad,
        descuento: me.descuento || 0,
        unidad_envase: me.arrayArticuloSeleccionado[0].unidad_envase,
        prectotal: me.prectotal
      });
      
      // Limpiar selección
      this.limpiarSeleccionArticulo();
    },
    
    limpiarSeleccionArticulo() {
      this.arrayArticuloSeleccionado = [];
      this.codigo = '';
      this.nombre_articulo = '';
      this.saldo_stock = '';
      this.cantidad = 1;
      this.precioseleccionado = '';
      this.prectotal = '';
    },
    
    calcularPrecioTotal() {
      // Asegurar que sean números
      const cantidad = parseFloat(this.cantidad) || 0;
      const precio = parseFloat(this.precioseleccionado) || 0;
      
      // Calcular y formatear
      this.prectotal = (cantidad * precio).toFixed(2);
    },
    
    // OPERACIONES COTIZACIÓN
    registrarCotizacion() {
      if (this.validarCotizacion()) {
        console.log("Rellene todos los campos");
        return;
      }
    
      let me = this;
    
      axios.post('/cotizacionventa/registrar', {
        'idcliente': this.idcliente,
        'impuesto': this.impuesto,
        'total': this.calcularTotal,
        'estado': this.estado_cotizacion,
        'n_validez': this.dias_validez,
        'tiempo_entrega': this.tiempo_entrega,
        'lugar_entrega': this.lugar_entrega,
        'forma_pago': this.forma_pago,
        'nota': this.nota,
        'idalmacen': this.idalmacen,
        'data': this.arrayDetalle
      }).then(function (response) {
        if (response.data.id > 0) {
          console.log("Cotización registrada con éxito.");
    
          // Imprimir el ticket
          me.imprimirTicket(response.data.id);
    
          // Reiniciar el formulario
          me.limpiarFormulario();
    
          // Actualizar el listado de cotizaciones
          me.listarCotizacion(1, '');
    
          // Cerrar el modal o detalle
          me.ocultarDetalle();
    
        } else {
          if (response.data.valorMaximo) {
            swal(
              'Aviso',
              'El valor de descuento no puede exceder el ' + response.data.valorMaximo,
              'warning'
            );
          } else {
            swal(
              'Aviso',
              response.data.caja_validado,
              'warning'
            );
          }
        }
      }).catch(function (error) {
        console.error("Error al registrar la cotización:", error);
      });
    },

    imprimirTicket(id) {
      axios.get('/cotizacionventa/imprimir/' + id, { responseType: 'blob' })
        .then(function (response) {
          console.log("Se generó el Ticket correctamente");
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    
    editarCotizacion() {
      let me = this;
      if (me.arrayDetalle.length === 0) {
        console.log("agregue articulo");
        return;
      }
      
      axios.put(`cotizacionventa/editar`, {
        'idcotizacionv': me.idcotizacionv,
        'idcliente': me.idcliente,
        'impuesto': me.impuesto,
        'total': me.calcularTotal,
        'estado': 'VENDIDO',
        'n_validez': me.dias_validez,
        'tiempo_entrega': me.tiempo_entrega,
        'lugar_entrega': me.lugar_entrega,
        'forma_pago': me.forma_pago,
        'nota': me.nota,
        'data': me.arrayDetalle
      }).then(function (response) {
        me.listado = 1;
        me.listarCotizacion(1, "", "");
      }).catch(function (error) {
        console.log('ERROR AL EDITAR', error);
      });
    },
    
    validarCotizacion() {
      let me = this;
      me.errorVenta = 0;
      me.errorMostrarMsjVenta = [];

      if (me.idcliente == 0) me.errorMostrarMsjVenta.push("Seleccione un Cliente");
      if (!me.impuesto) me.errorMostrarMsjVenta.push("Ingrese el impuesto de compra");
      if (me.arrayDetalle.length <= 0) me.errorMostrarMsjVenta.push("Ingrese detalles");

      if (me.errorMostrarMsjVenta.length) me.errorVenta = 1;
      return me.errorVenta;
    },
    
    limpiarFormulario() {
      this.idcliente = 0;
      this.clienteSeleccionado = '';
      this.nitcliente = '';
      this.impuesto = 0.18;
      this.prectotal = 0.0;
      this.estado_cotizacion = 'En espera';
      this.dias_validez = 7;
      this.tiempo_entrega = 'Inmediata';
      this.lugar_entrega = 'Deposito';
      this.forma_pago = 'Contado';
      this.nota = '';
      this.arrayDetalle = [];
      this.codigo = '';
      this.cantidad = 1;
      this.precio = 0;
      this.descuento = 0;
      this.limpiarSeleccionArticulo();
    },
    
    // ACCIONES DE UI
    mostrarDetalle(modelo, accion, data = []) {
      let me = this;
      switch (modelo) {
        case "venta":
          {
            switch (accion) {
              case 'cotizacion':
                {
                  me.listado = 0;
                  me.titulo = "Registrar nueva cotización";
                  me.fechaPedido = '';
                  me.fechaEntrega = '';
                  me.idalmacen = 1;
                  me.observacion = '';
                  me.forma_pago = 'Contado';
                  me.arrayDetalle = [];
                  me.proveedorSeleccionado = '';
                  break;
                }
              case 'editar':
                {
                  console.log("DATOS RECUPERADO:", data);
                  me.listado = 0;
                  me.titulo = "Editar Cotización Venta";
                  me.idcotizacionv = data['id'];
                  me.clienteSeleccionado = data['nombre'];
                  me.nitcliente = data['num_documento'];
                  me.telefono = data['telefono'];
                  me.idcliente = data['idcliente'];

                  me.dias_validez = data['plazo_entrega'];
                  me.tiempo_entrega = data['tiempo_entrega'] || 'Inmediata';
                  me.lugar_entrega = data['lugar_entrega'] || 'Deposito';
                  me.forma_pago = data['forma_pago'] || 'Contado';
                  me.nota = data['nota'] || '';
                  me.prectotal = data['total'];

                  me.verCotizacionDet(data);
                  break;
                }
            }
          }
          break;
      }
    },
    
    ocultarDetalle() {
      this.listado = 1;
      this.limpiarFormulario();
    },
    
    verCotizacionDet(data) {
      let idcotizacionv = data.id;
      let me = this;
      me.arrayCotizacionSeleccionado = data;
      
      var url = '/cotizacionventa/obtenerDetalles?idcotizacion=' + idcotizacionv;
      axios.get(url)
        .then(function (response) {
          var respuesta = response.data;
          me.arrayCotizacionVentDet = respuesta.detalles;
          me.arrayDetalle = respuesta.detalles;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    
    pdfVenta(id) {
      window.open('/cotizacionventa/pdf/' + id, '_blank');
    },
    
    // MODAL
    abrirModal() {
      this.listarArticulo("", "");
      this.arrayArticulo = [];
      this.modal = 1;
      this.tituloModal = 'Seleccione los artículos que desee';
    },
    
    cerrarModal() {
      this.modal = 0;
      this.tituloModal = '';
    },
    
    // ACTIVAR/DESACTIVAR
    desactivarCotizacion(id) {
      swal({
        title: '¿Está seguro de deshabilitar esta cotización?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar!',
        cancelButtonText: 'Cancelar',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false,
        reverseButtons: true
      }).then((result) => {
        if (result.value) {
          let me = this;

          axios.put('/cotizacionventa/desactivar', {
            'id': id
          }).then(function (response) {
            me.listarCotizacion(1, '', 'nombre');
            swal(
              'Desactivado!',
              'La cotización ha sido desactivada con éxito.',
              'success'
            )
          }).catch(function (error) {
            console.log(error);
          });
        }
      });
    },
    
    activarCotizacion(id) {
      swal({
        title: '¿Está seguro de habilitar esta cotización?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Aceptar!',
        cancelButtonText: 'Cancelar',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: false,
        reverseButtons: true
      }).then((result) => {
        if (result.value) {
          let me = this;

          axios.put('/cotizacionventa/activar', {
            'id': id
          }).then(function (response) {
            me.listarCotizacion(1, '', 'nombre');
            swal(
              'Activado!',
              'La cotización ha sido activada con éxito.',
              'success'
            )
          }).catch(function (error) {
            console.log(error);
          });
        }
      });
    },
    
    eliminarCotizacion(id) {
      axios.put('/cotizacionventa/eliminar', { id: id })
        .then(response => {
          console.log(response.data);
        })
        .catch(error => {
          console.error(error);
        });
    },
    
    // DETALLE Y VENTA
    abrirModalDetalles(venta) {
      this.showModalDetalle = true;
      this.verCotizacionDet(venta);
    },
    
    cerrarModalDetalles() {
      this.showModalDetalle = false;
    },
    
    abrirFormularioCotizacion(dato) {
      this.showRegistrarVenta = true;
      this.listado = 10;
      let idalmacen = dato.cotizacion.idalmacen;
      let arrayConIdsAlmacen = dato.detalles.map(objeto => {
        return { ...objeto, idalmacen: idalmacen };
      });
      this.arrayDetallesAComprar = arrayConIdsAlmacen;
      this.arrayCotizacionSeleccionado = dato.cotizacion;
      this.cerrarModalDetalles();
    },
    
    cerrarFormularioVenta() {
      this.showRegistrarVenta = false;
      this.listado = 1;
    },
    
    // Atajos de teclado - mantenido por compatibilidad

  },
  
  created() {
    this.listarPrecio();
  },
  
  mounted() {
    this.listarCotizacion(1, this.buscar, this.criterio);
    window.addEventListener('keydown', this.atajoButton);
    this.selectAlmacen();
  },
  
  beforeDestroy() {
    window.removeEventListener('keydown', this.atajoButton);
  }
}
</script>
<style>
/* Importar Bootstrap Icons */
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css");

/* Estilos generales */
.bg-gradient-primary {
  background: linear-gradient(135deg, #4e73df 0%, #3a57b8 100%);
}

.card {
  border: none;
  border-radius: 8px;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  margin-bottom: 1rem;
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 0.75rem 1.25rem;
}

/* Tablas */
.table th, .table td {
  padding: 0.75rem;
  vertical-align: middle;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.075);
}

/* Botones y grupos */
.btn-group .btn {
  border-radius: 4px;
  margin: 0 2px;
}

.btn:focus {
  box-shadow: none !important;
}

/* Divisor de secciones */
.section-divider {
  display: flex;
  align-items: center;
  color: #6c757d;
  font-size: 14px;
  margin: 20px 0;
}

.section-divider::before,
.section-divider::after {
  content: "";
  flex: 1;
  border-bottom: 1px solid #e9ecef;
}

.section-divider::before {
  margin-right: 0.5em;
}

.section-divider::after {
  margin-left: 0.5em;
}

/* Componente v-select */
.vs__dropdown-toggle {
  border-radius: 4px;
  padding: 2px 0;
  border-color: #ced4da;
}

.vs__selected {
  font-size: 0.9rem;
}

.vs__search::placeholder,
.vs__dropdown-toggle,
.vs__dropdown-menu {
  font-size: 0.9rem;
}

/* Modal */
.modal.mostrar {
  display: block !important;
  opacity: 1;
  background-color: rgba(0, 0, 0, 0.4);
}

/* Inputs y grupos */
.input-group-text {
  min-width: 40px;
  justify-content: center;
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
}

input:not([readonly]), select, textarea {
  background-color: #fff;
  border-color: #ced4da;
}

input:focus, select:focus, textarea:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Estilos responsivos */
@media (max-width: 767.98px) {
  .card-header {
    padding: 0.75rem 1rem;
  }
  
  .card-body {
    padding: 1rem;
  }
  
  .table th, .table td {
    padding: 0.5rem;
  }
  
  .btn-sm {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
  }
}

/* Badges */
.badge-success {
  background-color: #28a745;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}

.badge-danger {
  background-color: #dc3545;
}
</style>