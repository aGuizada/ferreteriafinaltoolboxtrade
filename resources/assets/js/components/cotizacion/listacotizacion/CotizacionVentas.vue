<template>
    <main class="main">
        <!-- <div class="m-2 p-2"></div> -->
        <div class="container-fluid">
            <!-- Ejemplo de tabla Listado -->
            <div class="card" v-if="!showRegistrarVenta">
                <!-- <div class="card-header" v-if="listado==1" > -->
                
                

            
                <template v-if="listado == 1">
                    <div class="card-body">
                        <div class="form-group row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" v-model="buscar" @keyup="listarCotizacion(1, buscar)"
                                    class="form-control" placeholder="Buscar por nombre, fecha o hora">
                            </div>
                        </div>
                    </div>


                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>

                                        <th>FECHA</th>
                                        <th>HORA</th>

                                        <th>CLIENTE</th>
                                        <th>NIT/CI</th>

                                        
                                        <th>TOTAL</th>
                                        <th>USUARIO</th>
                                        <th>EXPIRA EN</th>
                                        <th>NOTA/REF</th>
                                        <th class="fit-content">ACCIONES</th>


                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="venta in arrayVenta" :key="venta.id">
                                        <td v-text="venta.id"></td>

                                        <td>{{ new Intl.DateTimeFormat('es-ES').format(new Date(venta.fecha_hora)) }}
                                        </td>
                                        <td>{{ new Date(venta.fecha_hora).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            }) }}</td>
                                        <td v-text="venta.nombre"></td>
                                        <td v-text="venta.num_documento"></td>
                                        

                                        <td v-text="venta.total"></td>
                                        <td v-text="venta.usuario"></td>

                                        <td>{{ Math.floor((new Date(venta.validez) - new Date()) / (1000 * 60 * 60 *
                24)) + 1 }} dias</td>

                                       
                                        <td v-text="venta.nota"></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                              <!-- Botón para abrir detalles -->
                                              <button
                                                type="button"
                                                class="btn btn-sm p-button p-component p-button-icon-only"
                                                style="background-color: green; border-color: green; color: white;"
                                                @click="abrirModalDetalles(venta)"
                                              >
                                                <span class="pi pi-eye p-button-icon"></span>
                                                <span class="p-button-label">&nbsp;</span>
                                              </button>
                                          
                                              <!-- Botón para generar PDF -->
                                              <button
                                                type="button"
                                                class="btn btn-sm p-button p-component p-button-icon-only"
                                                style="background-color: blue; border-color: blue; color: white;"
                                                @click="pdfVenta(venta.id)"
                                              >
                                                <span class="pi pi-file-pdf p-button-icon"></span>
                                                <span class="p-button-label">&nbsp;</span>
                                              </button>
                                          
                                              <!-- Botón para editar -->
                                              <button
                                                type="button"
                                                class="btn btn-sm p-button p-component p-button-icon-only"
                                                style="background-color: orange; border-color: orange; color: white;"
                                                @click="mostrarDetalle('venta', 'editar', venta)"
                                              >
                                                <span class="pi pi-pencil p-button-icon"></span>
                                                <span class="p-button-label">&nbsp;</span>
                                              </button>
                                          
                                              <!-- Botón para desactivar -->
                                              <button
                                                v-if="venta.condicion"
                                                type="button"
                                                class="btn btn-sm p-button p-component p-button-icon-only"
                                                style="background-color: red; border-color: red; color: white;"
                                                @click="desactivarCotizacion(venta.id)"
                                              >
                                                <span class="pi pi-trash p-button-icon"></span>
                                                <span class="p-button-label">&nbsp;</span>
                                              </button>
                                          
                                              <!-- Botón para activar -->
                                              <button
                                                v-else
                                                type="button"
                                                class="btn btn-sm p-button p-component p-button-icon-only"
                                                style="background-color: green; border-color: green; color: white;"
                                                @click="activarCotizacion(venta.id)"
                                              >
                                                <span class="pi pi-check p-button-icon"></span>
                                                <span class="p-button-label">&nbsp;</span>
                                              </button>
                                            </div>

                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)"
                                        v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </template>
                <!-- ######################## Fin Listado inicio ###########################3-->
                <!-- Registrar cotizacion-->
                <template v-else-if="listado == 0">
                    <div class="card-body">
                        <div class="form-group row border">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">Cliente <span
                                            class="text-danger">*</span></label>
                                    <v-select :on-search="selectCliente" label="num_documento" :options="arrayCliente"
                                        placeholder="Num de documento..." :onChange="getDatosCliente"
                                        v-model="clienteSeleccionado">
                                    </v-select>
                                </div>
                            </div>
                       
                            <div class="col-md-2">
                                <label for="">NIT/CI</label>
                                <input type="text" class="form-control" v-model="nitcliente" readonly>
                            </div>
                        

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">Almacen <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" v-model="AlmacenSeleccionado"
                                        @change="getDatosAlmacen">
                                        <option value="0" disabled>Seleccione</option>
                                        <option v-for="opcion in arrayAlmacenes" :key="opcion.id" :value="opcion.id">{{
                opcion.nombre_almacen }}</option>
                                    </select>
                                </div>
                            </div>

                           


                        </div>
                        <div class="form-group row border">

                   

                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="form-inline">
                                        <button @click="abrirModal()" class="btn btn-primary">Buscar</button>
                                        <input type="text" class="form-control" v-model="codigo" ref="articuloRef"
                                            @keyup="buscarArticulo()" placeholder="Codigo del artículo">
                                        <!-- <input type="text" id="nombre_producto" readonly class="form-control"
                                            v-model="articulo"> -->
                                        <label for="">Shift + R</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card-body" v-if="nombre_articulo !== ''">
                                    <h3>{{ nombre_articulo }}</h3>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <img v-if="arrayArticuloSeleccionado.length > 0 && arrayArticuloSeleccionado[0].fotografia"
                                    :src="'img/articulo/' + arrayArticuloSeleccionado[0].fotografia + '?t=' + new Date().getTime()"
                                    width="50" height="50" ref="imagen" class="card-img" />
                                <img v-else
                                    src="https://cdn-icons-png.freepik.com/512/14/14795.png"
                                    alt="Imagen del Card" class="card-img">
                            </div>

                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>Total Uni/Stock</label>
                                    <input type="text" id="stock" style="color: red;" class="form-control"
                                        v-model="saldo_stock" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Precios(*)</label>
                                    <select class="form-control" v-model="precioseleccionado"
                                        @change="mostrarSeleccion">

                                        <option :value="precio_uno" v-if="arrayPrecios[0]">{{
                arrayPrecios[0].nombre_precio }}</option>
                                        <option :value="precio_dos" v-if="arrayPrecios[1]">
                                            {{ arrayPrecios[1].nombre_precio }}</option>
                                        <option :value="precio_tres" v-if="arrayPrecios[2]">
                                            {{ arrayPrecios[2].nombre_precio }}</option>
                                        <option :value="precio_cuatro" v-if="arrayPrecios[3]">
                                            {{ arrayPrecios[3].nombre_precio }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Precio Unitario<span style="color: red;"
                                            v-show="precio == 0">*</span></label>
                                    <input disabled type="number" value="0" step="any" class="form-control"
                                        v-model="precioseleccionado" ref="precioRef">
                                    <label for="" class="small-text">Shift + T</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Cantidad <span style="color: red;" v-show="cantidad == 0">*</span></label>
                                    <input type="number" value="0" class="form-control" v-model="cantidad"
                                        ref="cantidadRef">
                                    <label for="" class="small-text">Shift + Y</label>
                                </div>
                            </div>
                        

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Prec.Total</label>
                                    <input type="number" value="0" class="form-control" v-model="prectotal"
                                        ref="descuentoRef">
                                    <label for="" class="small-text">Shift + U</label>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <button @click="agregarDetalle()" class="btn btn-success form-control btnagregar">
                                        <i class="icon-plus"></i> Añadir item
                                    </button>
                                </div>
                            </div>
                    
                        </div>
                        <!--######################################-LISTADO CUANDO yA SE AGREGO CON LA "CANTIDAD" ##################-->
                        <div class="form-group row border">
                            <div class="table-responsive col-md-12">
                                <h6 class="text-center" style="font-weight: normal;"> DETALLE DE LA COTIZACIÒN5</h6>
                                <table class="table table-bordered table-striped table-sm">

                                    <thead>
                                        <tr>
                                            <th>Opciones</th>
                                            <th>Codigo</th>
                                            <th>Artículo</th>
                                            <th>Precio/U</th>
                                            <th>Unid/Paq</th>
                                            <th>Paquetes</th>
                                            <th>Total</th>
                                        
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayDetalle.length">
                                        <tr v-for="(detalle, index) in arrayDetalle" :key="detalle.id">
                                            <td>
                                                <button @click="eliminarDetalle(index)" type="button"
                                                    class="btn btn-danger btn-sm">
                                                    <i class="icon-close"></i>
                                                </button>
                                            </td>
                                            <td v-text="detalle.codigo"></td>
                                            <td v-text="detalle.nombre_articulo"></td>
                                            <td v-text="detalle.precioseleccionado"></td>
                                            <td v-text="detalle.unidad_envase"></td>
                                            <td v-text="detalle.cantidad"></td>
                                            <td v-text="detalle.prectotal"></td>
                                           
                                        </tr>
                                      
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="6">No hay articulos agregados</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-2">
                                <label for="">Dias de validez</label>
                                <input type="number" class="form-control" v-model="dias_validez">
                            </div>
                          
                            <div class="col-md-2">
                                <label for="">Nota</label>
                                <input type="text" class="form-control" v-model="nota">
                            </div>
                         
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12 text-right">
                                <button type="button" @click="ocultarDetalle()"
                                    class="btn btn-secondary">Cerrar</button>
                                <!-- <button v-if="titulocard=='REGISTRAR COTIZACIÒN'" type="button" class="btn btn-primary" @click="registrarCotizacion()">Registrar Cotización</button> -->
                                <button v-if="idcotizacionv != ''" type="button" class="btn btn-primary"
                                    @click="editarCotizacion()">Editar Cotización</button>
                                <button v-else type="button" class="btn btn-primary"
                                @click.prevent="registrarCotizacion()"
                                >Registrar Cotización</button>

                            </div>
                        </div>

                    </div>

                </template>
                <!-- Fin registro-->
                <!-- ###-ELIMINAR-###-INICIO REGISTRAR VENTA -->
                <template v-else-if="listado == 3">
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="button" @click="ocultarDetalle()"
                                    class="btn btn-secondary">Cerrar</button>
                                <button type="button" class="btn btn-primary" @click="registrarVenta()">Registrar
                                    Venta</button>
                            </div>
                        </div>
                    </div>
                </template>
                <!-- FIN REGISTRAR VENTA -->


            </div>
            <!-- Fin ejemplo de tabla Listado -->
        </div>
        <!--################## Inicio del modal LISTAR /PRODUCTO DE INVENTARIO ######################-->
        <div class="modal fade" tabindex="-1" :class="{ 'mostrar': modal }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal"></h4>
                        <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterioA">
                                        <option value="nombre">Nombre</option>
                                        <option value="descripcion">Descripción</option>
                                        <option value="codigo">Código</option>
                                    </select>
                                    <input type="text" v-model="buscarA" @keyup="listarArticulo(buscarA, criterioA)"
                                        class="form-control" placeholder="Texto a buscar">
                                    <!--button type="submit" @click="listarArticulo(buscarA, criterioA)"
                                        class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button-->
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Precio Venta</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="articulo in arrayArticulo" :key="articulo.id">
                                        <td>
                                            <button type="button" @click="agregarDetalleModal(articulo)"
                                                class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                        </td>
                                        <td v-text="articulo.codigo"></td>
                                        <td v-text="articulo.nombre"></td>
                                        <td v-text="articulo.nombre_categoria"></td>
                                        <td v-text="articulo.precio_venta"></td>
                                        <td v-text="articulo.saldo_stock"></td>
                                        <td>
                                            <div v-if="articulo.condicion">
                                                <span class="badge badge-success">Activo</span>
                                            </div>
                                            <div v-else>
                                                <span class="badge badge-danger">Desactivado</span>
                                            </div>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                       
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!--Fin del modal-->
        <detallecotizacionventa v-if="showModalDetalle" @cerrar="cerrarModalDetalles"
            @abrirVenta="abrirFormularioCotizacion" :arrayCotizacionSeleccionado="arrayCotizacionSeleccionado"
            :arrayCotizacionVentDet="arrayCotizacionVentDet">
        </detallecotizacionventa>
        <registrarventa v-if="showRegistrarVenta" :arrayDetalleCotizacion="arrayDetallesAComprar"
            :arrayCotizacionSeleccionado="arrayCotizacionSeleccionado" @cerrar="cerrarFormularioVenta">
        </registrarventa>

 
    </main>
</template>

<script>
import vSelect from 'vue-select';
export default {
    data() {
        return {
            //-----
            showRegistrarVenta: false,
            titulo: 'Cotizacion de Venta',
            telefono: '',
            AlmacenSeleccionado: 1,
            idalmacen: 1,
            arrayAlmacenes: [],
            arrayArticuloSeleccionado: [],//ALA,ACENAR LO SELECCIONADO
            nombre_articulo: '',
            saldo_stock: '',
            // precio_costo_unid:'',
            // precio_costo_paq:'',
            unidad_envase: '',
            prectotal: '',
            estado_cotizacion: 'En espera',
            clienteSeleccionado: '',
            idcotizacionv: '',
            arrayCotizacionSeleccionado: {},
            arrayCotizacionVentDet: [],
            showModalDetalle: false,
            //-----PRECIOS- AUMENTE 3/OCTUBRE--------
            precioseleccionado: '',
            //precio : '',
            arrayPrecios: [],
            nombre_precio: '',
            precio_uno: '',
            precio_dos: '',
            precio_tres: '',
            precio_cuatro: '',
            //------

            idcotizacion: 0,
            actualizarIva: '',

            rolUsuario: '',

            titulocard: '',
            dias_validez: 1,

            tiempo_entrega: "Inmediata",
            lugar_entrega: "Deposito",
            forma_pago: '',
            //forma_pago:"Al contado",
            nota: "",
            options_print: [

                { value: "opcion1", label: "Cotizaciòn A" },
                { value: "opcion2", label: "Cotizaciòn B" },
                { value: "opcion3", label: "Cotizaciòn C" }
            ],
            imprimir: "",
            venta_id: 0,
            //idcliente: '',
            idcliente: 0,
            nitcliente: "",
            cliente: '',
            tipo_comprobante: 'BOLETA',
            serie_comprobante: '',
            num_comprobante: '',
            impuesto: 0.18,
            total: 0.0,
            totalImpuesto: 0.0,
            totalParcial: 0.0,
            arrayVenta: [],
            arrayCliente: [],
            arrayDetalle: [],
            listado: 1,
            modal: 0,
            tituloModal: '',
            tipoAccion: 0,
            errorVenta: 0,
            errorMostrarMsjVenta: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            criterio: 'num_comprobante',
            buscar: '',
            criterioA: 'nombre',
            buscarA: '',
            arrayArticulo: [],
            idarticulo: 0,
            codigo: '',
            //articulo: '',
            precio: 0,
            cantidad: 0,
            descuento: 0,
            stock: 0,
            valorMaximoDescuento: '',
        }
    },
    watch: {
        // Observa cambios en cantidad y precio para calcular el valor total
        cantidad() {
            this.calcularPrecioTotal();
        },
        precioseleccionado() {
            this.calcularPrecioTotal();
        },
    },
    components: {
        vSelect
    },
    computed: {
        isActived: function () {
            return this.pagination.current_page;
        },

        //Calcula los elementos de la paginación
        pagesNumber: function () {
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

        calcularTotal: function () {
            var resultado = 0.0;
            for (var i = 0; i < this.arrayDetalle.length; i++) {
                resultado = resultado + (this.arrayDetalle[i].precio * this.arrayDetalle[i].cantidad - this.arrayDetalle[i].descuento)
            }
            return resultado;
        }
    },
    methods: {

        listarCotizacion(pagina, buscar) {
            let criterio = '';
            
            // Determinar el criterio según el contenido de la búsqueda
            if (/^\d+$/.test(buscar)) {
                // Si la búsqueda es un número (por ejemplo, número de comprobante)
                criterio = 'num_comprobante';
            } else if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(buscar)) {
                // Si la búsqueda es una fecha y hora en formato 'YYYY-MM-DD HH:mm:ss'
                criterio = 'fecha_hora';
            } else {
                // Si es cualquier otra cosa, se asume que es un nombre o tipo de comprobante
                criterio = 'tipo_comprobante';
            }
        
            // Ahora puedes realizar la búsqueda según el 'criterio' y el valor de 'buscar'
            // Realiza la búsqueda según el 'criterio' y el valor de 'buscar'
        },

        logout() {
            axios.post('/logout')
                .then(response => {
                    window.location = '/'; // Redirigir al usuario a la página de inicio después del cierre de sesión
                })
                .catch(error => {
                    console.error('Hubo un error al cerrar la sesión', error);
                });
        },

        calcularPrecioTotal() {
            // Calcula el valor total multiplicando cantidad por precio
            this.prectotal = this.cantidad * this.precioseleccionado;
            console.log("CALCULADO", this.prectotal);
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
            console.log("datos ", dato);

            console.log("vue ", this.showRegistrarVenta)
            console.log("cotizACION ", this.arrayCotizacionSeleccionado)
            console.log("detalles", this.arrayDetallesAComprar)
            this.cerrarModalDetalles();
        },
        datosConfiguracion() {
            let me = this;
            var url = '/configuracion/editar';

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.actualizarIva = respuesta.configuracionTrabajo.actualizarIva;
                console.log(me.actualizarIva);

            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        recuperarIdRol() {
            this.rolUsuario = window.userData.rol;
            console.log('ID_ROL: ' + this.rolUsuario);
        },
        //--no se esX4a usando hasX4a el momento--
        registrarVenta(id) {
            if (this.validarVenta()) {
                console.log("Completa todos los campos");
                return;
            }
            console.log("Validado xd");
            let me = this;
            console.log(me.idcliente);
            console.log(me.tipo_comprobante);
            console.log(me.num_comprobante);
            console.log(me.impuesto);
            console.log("=========================");
            console.log(me.idcotizacion);
            console.log("=========================");


            axios.post('/venta/registrar', {
                'idcliente': this.idcliente,
                'tipo_comprobante': this.tipo_comprobante,
                'serie_comprobante': this.serie_comprobante,
                'num_comprobante': this.num_comprobante,
                'impuesto': this.impuesto,
                'total': this.total,
                'data': this.arrayDetalle

            }).then(function (response) {
                console.log(me.idcotizacion);
                me.eliminarCotizacion(me.idcotizacion);
                // axios.delete('/cotizacionventa/eliminar/' + { id:  me.idcotizacion})
                //     .then(response => {
                //         // Manejar la respuesta exitosa aquí, si es necesario
                //         console.log(response.data);
                //         console.log("Eliminada");
                //     })
                //     .catch(error => {
                //         // Manejar el error aquí, si es necesario
                //         console.error(error);
                //     });
                //console.log(response.data.id);
                if (response.data.id > 0) {
                    me.listado = 1;
                    me.listarCotizacion(1, '', '');
                    me.idproveedor = 0;
                    me.tipo_comprobante = 'BOLETA';
                    me.serie_comprobante = '';
                    me.num_comprobante = '';
                    me.impuesto = 0.13;
                    me.total = 0.0;
                    me.idarticulo = 0;
                    me.articulo = '';
                    me.cantidad = 0;
                    me.precio = 0;
                    me.stock = 0;
                    me.codigo = '';
                    me.descuento = 0;
                    me.arrayDetalle = [];

                    window.open('/venta/pdf/' + response.data.id);
                } else {
                    if (response.data.valorMaximo) {
                        //alert('El valor de descuento no puede exceder el '+ response.data.valorMaximo);
                        swal(
                            'Aviso',
                            'El valor de descuento no puede exceder el ' + response.data.valorMaximo + ' %',
                            'warning'
                        )
                        return;
                    } else {
                        //alert(response.data.caja_validado); 
                        swal(
                            'Aviso',
                            response.data.caja_validado,
                            'warning'
                        )
                        return;
                    }
                    //console.log(response.data.valorMaximo)
                }

            }).catch(function (error) {
                console.log(error);
            });
            console.log("SE vendio");
        },

        eliminarCotizacion(id) {
            axios.put('/cotizacionventa/eliminar', { id: id })
                .then(response => {
                    // Manejar la respuesta exitosa aquí, si es necesario
                    console.log(response.data);
                })
                .catch(error => {
                    // Manejar el error aquí, si es necesario
                    console.error(error);
                });
        },
        abrirVenta(id) {


            let me = this;
            me.idcotizacion = id;
            console.log("==============");
            console.log(me.idcotizacion);
            console.log("==============");

            me.listado = 3
            me.titulocard = "REGISTRAR VENTA";;

            //Obtener datos del ingreso
            var arrayVentaT = [];
            var url = '/cotizacionventa/obtenerCabecera?id=' + id;

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                arrayVentaT = respuesta.cotizacion;
                console.log("Cliente");

                console.info(arrayVentaT[0]['nombre']);
                console.log("Cliente");

                me.cliente = arrayVentaT[0]['nombre'];
                me.idcliente = arrayVentaT[0]['idcliente'];
                me.arrayCliente = [
                    {
                        'id': me.idcliente,
                        'nombre': me.cliente,
                    }
                ];

                console.log(me.arrayCliente);
                console.log("XD");
                me.impuesto = arrayVentaT[0]['impuesto'];
                me.total = arrayVentaT[0]['total'];
            })
                .catch(function (error) {
                    console.log(error);
                });

            //obtener datos de los detalles
            var url = '/cotizacionventa/obtenerDetalles?idcotizacion=' + id;

            axios.get(url).then(function (response) {
                //console.log(response);
                var respuesta = response.data;
                me.arrayDetalle = respuesta.detalles;
                console.log("=======");
                console.log(me.arrayDetalle);

            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        desactivarCotizacion(id) {
            swal({
                title: '¿Esta seguro de deshabilitar esta cotizaciòn?',
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
                            'La cotizaciòn ha sido desactivado con éxito.',
                            'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });


                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },
        activarCotizacion(id) {
            swal({
                title: '¿Esta seguro de habilitar esta cotizaciòn?',
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
                            'La cotizaciòn ha sido activado con éxito.',
                            'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });


                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },
        atajoButton: function (event) {
            //console.log(event.keyCode);
            //console.log(event.ctrlKey);
            if (event.shiftKey && event.keyCode === 81) {
                event.preventDefault();
                this.$refs.impuestoRef.focus();
            }
            if (event.shiftKey && event.keyCode === 87) {
                event.preventDefault();
                this.$refs.serieComprobanteRef.focus();
            }
            if (event.shiftKey && event.keyCode === 69) {
                event.preventDefault();
                this.$refs.numeroComprobanteRef.focus();
            }
            if (event.shiftKey && event.keyCode === 82) {
                event.preventDefault();
                this.$refs.articuloRef.focus();
            }
            if (event.shiftKey && event.keyCode === 84) {
                event.preventDefault();
                this.$refs.precioRef.focus();
            }
            if (event.shiftKey && event.keyCode === 89) {
                event.preventDefault();
                this.$refs.cantidadRef.focus();
            }
            if (event.shiftKey && event.keyCode === 85) {
                event.preventDefault();
                this.$refs.descuentoRef.focus();
            }
        },
        listarCotizacion(page, buscar, criterio) {
            let me = this;
            var url = '/cotizacionventa?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;

                me.arrayVenta = respuesta.cotizacion_venta.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        selectCliente(numero) {
            let me = this;
            var url = '/cliente/selectClientePorNumero?numero=' + numero;
            axios.get(url).then(function (response) {
                let respuesta = response.data;
                q: numero;
                me.arrayCliente = respuesta.clientes;
                console.log(me.arrayCliente);
            }).catch(function (error) {
                console.log(error);
            });
        },

        selectCliente2(search, loading) {
            let me = this;
            loading(true)
            var url = '/cliente/selectCliente?filtro=' + search;
            axios.get(url).then(function (response) {
                //console.log(response);
                let respuesta = response.data;
                q: search
                me.arrayCliente = respuesta.clientes;
                console.log("CLIENTE!!", me.arrayCliente);
                loading(false)
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        getDatosCliente(val1) {
            let me = this;
            me.loading = true;
            //me.idcliente = val1.id;
            //------nombre---
            if (val1 && val1.id) {
                me.idcliente = val1.id;
                me.clienteSeleccionado = val1.nombre;
                me.nitcliente = val1.num_documento;
                console.log(val1.num_documento);
                me.telefono = val1.telefono;
                console.log("Telefono", val1.telefono);
            }
            //-----
            // me.nitcliente=val1.num_documento;
            // console.log(val1.num_documento);
            // me.telefono= val1.telefono;
            // console.log("Telefono",val1.telefono);
        },

        buscarArticulo() {
            let me = this;
            var url = '/articulo/buscarArticuloVenta?filtro=' + me.codigo;

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayArticulo = respuesta.articulos;

                if (me.arrayArticulo.length > 0) {
                    me.articulo = me.arrayArticulo[0]['nombre'];
                    me.idarticulo = me.arrayArticulo[0]['id'];
                    me.precio = me.arrayArticulo[0]['precio_venta'];
                    me.stock = me.arrayArticulo[0]['stock'];
                }
                else {
                    me.articulo = 'No existe este articulo';
                    me.idarticulo = 0;
                }
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        pdfVenta(id) {
            window.open('/cotizacionventa/pdf/' + id, '_blank');
        },
        cambiarPagina(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            //Envia la petición para visualizar la data de esa página
            me.listarCotizacion(page, buscar, criterio);
        },
        encuentra(id) {
            var sw = 0;
            for (var i = 0; i < this.arrayDetalle.length; i++) {
                if (this.arrayDetalle[i].idarticulo == id) {
                    sw = true;
                }
            }
            return sw;
        },
        eliminarDetalle(index) {
            let me = this;
            me.arrayDetalle.splice(index, 1);
        },
        agregarDetalle() {
            let me = this;
            // if (me.idarticulo == 0 || me.cantidad == 0 || me.precio == 0) {

            // }
            console.log("----------Almacen seleccionado!!---------");
            console.log(me.AlmacenSeleccionado);
            console.log('TODO', me.arrayDetalle);
            console.log("----------Almacen seleccionado!!----------");
            if (me.arrayArticuloSeleccionado.length == 0 || me.cantidad == 0) {
                console.log("Seleccione un producto o verifique la cantidad");

            } else {
                if (me.encuentra(me.idarticulo)) {
                    swal({
                        type: 'error',
                        title: 'Error...',
                        text: 'Este Artículo ya se encuentra agregado!',
                    })
                } else {
                    if (me.cantidad > me.saldo_stock) {
                        swal({
                            type: 'error',
                            title: 'Error...',
                            text: 'No hay stock disponible!',
                        })
                    } else {
                        me.arrayDetalle.push({
                            idarticulo: me.arrayArticuloSeleccionado[0].idarticulo,
                            codigo: me.arrayArticuloSeleccionado[0].codigo,
                            nombre_articulo: me.arrayArticuloSeleccionado[0].nombre_articulo,
                            precioseleccionado: me.precioseleccionado,
                            cantidad: me.cantidad,
                            descuento: me.descuento,
                            unidad_envase: me.arrayArticuloSeleccionado[0].unidad_envase,
                            prectotal: me.prectotal,
                            // precio : me.arrayArticuloSeleccionado[0].precio,
                            // cantidad: me.cantidad,
                            // total: me.Sumatotal,

                            // idarticulo: me.idarticulo,
                            // articulo: me.articulo,
                            // cantidad: me.cantidad,
                            // precio: me.precio,
                            // descuento: me.descuento,
                            // stock: me.stock
                        });
                        // me.codigo = '';
                        // me.idarticulo = 0;
                        // me.articulo = '';
                        // me.cantidad = 0;
                        // me.precio = 0;
                        // me.descuento = 0;
                        // me.stock = 0
                    }
                }

            }

        },
        //----AGREGAR LOS DAtOS A LOS INPUT--
        agregarDetalleModal(data = []) {
            let me = this;
            if (me.encuentra(data['id'])) {
                swal({
                    type: 'error',
                    title: 'Error...',
                    text: 'Este Artículo ya se encuentra agregado!',
                })
            } else {
                console.log("==========Agregando A los inpuT como precio-==============");
                console.log(data);
                console.log("=============hasta aqui=================");
                me.arrayArticuloSeleccionado = [{
                    idarticulo: data['id'],
                    codigo: data['codigo'],
                    saldo_stock: data['saldo_stock'],
                    nombre_articulo: data['nombre'],
                    unidad_envase: data['unidad_envase'],
                    // precio_costo_paq: data['precio_costo_paq'],
                    // precio_costo_unid: data['precio_costo_unid'],
                    fotografia: data['fotografia'],
                    precio_uno: data['precio_uno'],
                    precio_dos: data['precio_dos'],
                    precio_tres: data['precio_tres'],
                    precio_cuatro: data['precio_cuatro']
                }]
                me.codigo = me.arrayArticuloSeleccionado[0].codigo;
                me.saldo_stock = me.arrayArticuloSeleccionado[0].saldo_stock;
                me.nombre_articulo = me.arrayArticuloSeleccionado[0].nombre_articulo;
                me.unidad_envase = me.arrayArticuloSeleccionado[0].unidad_envase;
                me.precio_uno = me.arrayArticuloSeleccionado[0]['precio_uno'];
                me.precio_dos = me.arrayArticuloSeleccionado[0]['precio_dos'];
                me.precio_tres = me.arrayArticuloSeleccionado[0]['precio_tres'];
                me.precio_cuatro = me.arrayArticuloSeleccionado[0]['precio_cuatro'];
                // me.arrayDetalle.push({
                //     idarticulo: data['id'],
                //     articulo: data['nombre'],
                //     cantidad: 1,
                //     precio: data['precio_venta'],
                //     descuento: 0,
                //     stock: data['stock']
                // });
            }
        },
        listarArticulo(buscar, criterio) {
            let me = this;
            var url = '/articulo/listarArticuloVenta?buscar=' + buscar + '&criterio=' + criterio + '&idAlmacen=' + this.idalmacen;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                console.log("LLEGA!!", respuesta);
                me.arrayArticulo = respuesta.articulos;
                console.log("LLEGACOTIZACIONVENTA!!", me.arrayArticulo);
            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        imprimirTicket(id) {
            axios.get('/cotizacionventa/imprimir/' + id, { responseType: 'blob' })
                .then(function (response) {
                    //window.location.href = "docs/ticket.pdf";  esto sirve para ver lo que se va a imprimir
                    //const fileURL = 'docs/ticket.pdf';         pero si esta comentado, no aparece, se imprime automaticamente
                    //window.open(fileURL, '_blank');            bueno, eso suponemos. Att: los practicantes
                    console.log("Se generó el Ticket correctamente");
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        registrarCotizacion() {
    if (this.validarCotizacion()) {
        console.log("Rellene todos los campos");
        return;
    }

    let me = this;

    axios.post('/cotizacionventa/registrar', {
        'idcliente': this.idcliente,
        'impuesto': this.impuesto,
        'total': this.prectotal,
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
            me.reiniciarFormulario();

            // Actualizar el listado de cotizaciones
            me.listarCotizacion(1, '', '');

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

reiniciarFormulario() {
    this.idcliente = '';
    this.impuesto = 0.18;
    this.prectotal = 0.0;
    this.estado_cotizacion = '';
    this.dias_validez = '';
    this.tiempo_entrega = '';
    this.lugar_entrega = '';
    this.forma_pago = '';
    this.nota = '';
    this.idalmacen = '';
    this.arrayDetalle = [];
    this.codigo = '';
    this.articulo = '';
    this.cantidad = 0;
    this.precio = 0;
    this.stock = 0;
    this.descuento = 0;
},

        validarCotizacion() {
            let me = this;
            me.errorVenta = 0;
            me.errorMostrarMsjVenta = [];
            var art;

            me.arrayDetalle.map(function (x) {
                if (x.cantidad > x.stock) {
                    art = x.articulo + " Stock insuficiente";
                    me.errorMostrarMsjVenta.push(art);
                }
            });

            if (me.idcliente == 0) me.errorMostrarMsjVenta.push("Seleccione un Cliente");
            if (!me.impuesto) me.errorMostrarMsjVenta.push("Ingrese el impuesto de compra");
            if (me.arrayDetalle.length <= 0) me.errorMostrarMsjVenta.push("Ingrese detalles");

            if (me.errorMostrarMsjVenta.length) me.errorVenta = 1;
            console.log(me.errorVenta);
            return me.errorVenta;
        },
        validarVenta() {
            let me = this;
            me.errorVenta = 0;
            me.errorMostrarMsjVenta = [];
            var art;

            me.arrayDetalle.map(function (x) {
                if (x.cantidad > x.stock) {
                    art = x.articulo + " Stock insuficiente";
                    me.errorMostrarMsjVenta.push(art);
                }
            });

            if (me.idcliente == 0) me.errorMostrarMsjVenta.push("Seleccione un Cliente");
            if (me.tipo_comprobante == 0) me.errorMostrarMsjVenta.push("Sleccione el Comprobante");
            if (!me.num_comprobante) me.errorMostrarMsjVenta.push("Ingrese el numero de comprobante");
            if (!me.impuesto) me.errorMostrarMsjVenta.push("Ingrese el impuesto de compra");
            if (me.arrayDetalle.length <= 0) me.errorMostrarMsjVenta.push("Ingrese detalles");

            if (me.errorMostrarMsjVenta.length) me.errorVenta = 1;

            return me.errorVenta;
        },
        //---MODAL--
        mostrarDetalle(modelo, accion, data = []) {
            let me = this;
            switch (modelo) {
                case "venta":
                    {
                        switch (accion) {
                            case 'cotizacion':
                                {
                                    me.listado = 0;
                                    me.titulo = "Registar nuevo pedido";

                                    //me.idproveedor = 0;
                                    //me.idproveedor = '';
                                    me.fechaPedido = '';
                                    me.fechaEntrega = '';
                                    me.idalmacen = 1;
                                    me.observacion = '';
                                    me.forma_pago = '';
                                    me.arrayDetalle = [];
                                    me.proveedorSeleccionado = '';
                                    //this.inicializarFechas();
                                    break;
                                }
                            case 'editar':
                                {
                                    console.log("DATOS RECUPERADO!!:", data);
                                    me.listado = 0;//abrir el Template de editar
                                    me.titulo = "Editar Cotizacion Venta";
                                    me.idcotizacionv = data['id'];
                                    me.clienteSeleccionado = data['nombre'];
                                    me.nitcliente = data['num_documento'];
                                    me.telefono = data['telefono'];
                                    me.idcliente = data['idcliente'];
                                    console.log('IDCLIENTE', me.idcliente);

                                    me.dias_validez = data['plazo_entrega'];
                                    me.tiempo_entrega = data['tiempo_entrega'];
                                    me.lugar_entrega = data['lugar_entrega'];
                                    me.forma_pago = data['forma_pago'];
                                    me.nota = data['nota'];
                                    me.prectotal = data['total'];

                                    //me.label = data['nombre_proveedor'];
                                    //selectProveedor(search, loading);
                                    me.verCotizacionDet(data);
                                    break;
                                }
                        }
                    }
            }
        },
        getDatosAlmacen() {
            let me = this;
            if (me.AlmacenSeleccionado !== '') {
                me.loading = true;
                me.idalmacen = Number(me.AlmacenSeleccionado);
                console.log('IDalmacen: ' + me.idalmacen);
            }
        },
        selectAlmacen() {
            let me = this;
            var url = '/almacen/selectAlmacen';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayAlmacenes = respuesta.almacenes;

            }).catch(function (error) {
                console.log(error);
            });
        },
        listarPrecio() {
            let me = this;
            var url = '/precios';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayPrecios = respuesta.precio.data;
                console.log('PRECIOS', me.arrayPrecios);
                //me.precioCount = me.arrayBuscador.length;
            }).catch(function (error) {
                console.log(error);
            });
        },
        mostrarSeleccion() {
            console.log('Precio seleccionado:', this.precioseleccionado);
        },
        verCotizacionDet(data) {
            let idcotizacionv = data.id;
            let me = this;

            me.arrayCotizacionSeleccionado = data;//--para enviar a oTro Vuejs
            console.log("1_RECUPERA DATO EDITAR!!", me.arrayCotizacionSeleccionado);
            //console.log("IDCO◘4IZACIO##", me.idcotizacionv);

            console.log("IDcotizacion antes de la solicitud axios:", idcotizacionv);
            var url = '/cotizacionventa/obtenerDetalles?idcotizacion=' + idcotizacionv;
            axios.get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    //console.log("RESPONSE!!#",respuesta);
                    me.arrayCotizacionVentDet = respuesta.detalles; // Para enViar a regis◘4rar a otro Vuejs
                    console.log("2_RECUPERA DATO EDI◘4AR!!", me.arrayCotizacionVentDet);
                    me.arrayDetalle = respuesta.detalles;
                    console.log("3_RECUPERA DATO EDI◘4AR!!", me.arrayDetalle);

                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        //----------para enViar a otro Vuejs----
        abrirModalDetalles(venta) {
            this.showModalDetalle = true;
            this.verCotizacionDet(venta);
        },
        cerrarFormularioVenta() {
            this.showRegistrarVenta = false;
            this.listado = 1;
        },
        cerrarModalDetalles() {
            this.showModalDetalle = false;
        },
        //------------hasta aqui------------------
        editarCotizacion() {

            let me = this;
            if (me.arrayDetalle.length === 0) {
                console.log("agregue articulo");
                return;
            };
            console.log("Valores enviados:", {
                idcotizacionv: me.idcotizacionv,
                idcliente: me.idcliente,
                impuesto: me.impuesto,
                total: me.prectotal,
                estado: 'VENDIDO',
                n_validez: me.dias_validez,
                tiempo_entrega: me.tiempo_entrega,
                lugar_entrega: me.lugar_entrega,
                forma_pago: me.forma_pago,
                nota: me.nota,
                data: me.arrayDetalle
            });
            axios.put(`cotizacionventa/editar`, {
                'idcotizacionv': me.idcotizacionv, // Agrega el idpedido al cuerpo del request
                'idcliente': me.idcliente,
                'impuesto': me.impuesto,
                'total': me.prectotal,
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
        // mostrarDetalle(titulo,ventaid) {
        //     let me = this;
        //     me.listado = 0;
        //     me.titulocard=titulo;

        //     if (me.titulocard=="REGISTRAR COTIZACIÒN"){
        //         me.idproveedor = 0;
        //         me.impuesto = 0.18;
        //         me.total = 0.0;
        //         me.idarticulo = 0;
        //         me.articulo = '';
        //         me.cantidad = 0;
        //         me.precio = 0;
        //         me.arrayDetalle = [];
        //     }else{

        //         me.actualizarCotizacion(ventaid);


        //     }


        // },
        ocultarDetalle() {
            this.listado = 1;
            this.num_comprobante = '';
            this.cliente = '';
            this.arrayArticulo = [];
            this.serie_comprobante = '';
            this.idcotizacionv = '';
            
        },
        actualizarCotizacion(id) {
            let me = this;

            //Obtener datos del ingreso
            var arrayVentaT = [];
            var url = '/cotizacionventa/obtenerCabecera?id=' + id;

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                arrayVentaT = respuesta.cotizacion;
                console.log(me.arrayCliente);
                me.cliente = arrayVentaT[0]['nombre'];
                me.idcliente = arrayVentaT[0]['idcliente'];
                me.arrayCliente = [
                    {
                        'id': me.idcliente,
                        'nombre': me.cliente,
                    }

                ];


                me.impuesto = arrayVentaT[0]['impuesto'];
                me.total = arrayVentaT[0]['total'];
                console.log(me.arrayCliente);

            })
                .catch(function (error) {
                    console.log(error);
                });

            //obtener datos de los detalles
            var url = '/cotizacionventa/obtenerDetalles?id=' + id;

            axios.get(url).then(function (response) {
                //console.log(response);
                var respuesta = response.data;
                me.arrayDetalle = respuesta.detalles;

            })
                .catch(function (error) {
                    console.log(error);
                });
        },


        // verVenta(id) {
        //     let me = this;
        //     me.titulocard="COTIZACION";
        //     me.listado = 2;

        //     //Obtener datos del ingreso
        //     var arrayVentaT = [];
        //     var url = '/cotizacionventa/obtenerCabecera?id=' + id;

        //     axios.get(url).then(function (response) {
        //         var respuesta = response.data;
        //         arrayVentaT = respuesta.cotizacion;

        //         me.cliente = arrayVentaT[0]['nombre'];
        //         me.impuesto = arrayVentaT[0]['impuesto'];
        //         me.total = arrayVentaT[0]['total'];
        //     })
        //         .catch(function (error) {
        //             console.log(error);
        //         });

        //     //obtener datos de los detalles
        //     var url = '/cotizacionventa/obtenerDetalles?id=' + id;

        //     axios.get(url).then(function (response) {
        //         //console.log(response);
        //         var respuesta = response.data;
        //         me.arrayDetalle = respuesta.detalles;

        //     })
        //         .catch(function (error) {
        //             console.log(error);
        //         });
        // },

        cerrarModal() {
            this.modal = 0;
            this.tituloModal = '';

        },
        abrirModal() {
            this.listarArticulo("", "");
            this.arrayArticulo = [];
            this.modal = 1;
            this.tituloModal = 'Seleccione los articulos que desee';

        },

        desactivarVenta(id) {
            swal({
                title: 'Esta seguro de anular esta venta?',
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

                    axios.put('/venta/desactivar', {
                        'id': id
                    }).then(function (response) {
                        me.listarCotizacion(1, '', 'num_comprobante');
                        swal(
                            'Anulado!',
                            'La venta ha sido anulado con éxito.',
                            'success'
                        )
                    }).catch(function (error) {
                        console.log(error);
                    });


                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {

                }
            })
        },

    },
    created() {
        this.listarPrecio();
    },
    mounted() {
        this.listarCotizacion(1, this.buscar, this.criterio);
        window.addEventListener('keydown', this.atajoButton);
        this.selectAlmacen();

    }
}
</script>
<style>
.fit-content {
    width: 1%;
    white-space: nowrap;
}

.modal-content {
    width: 100% !important;
    position: absolute !important;
}

.mostrar {
    display: list-item !important;
    opacity: 1 !important;
    position: absolute !important;
    background-color: #3c29297a !important;
}

.div-error {
    display: flex;
    justify-content: center;
}

.text-error {
    color: red !important;
    font-weight: bold;
}

.small-text {
    font-size: 12px;
    white-space: nowrap;
    color: darkgrey;
}

@media (min-width: 600px) {
    .btnagregar {
        margin-top: 2rem;
    }
}

.card-img {
    width: 120px;
    height: auto;
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}
</style>
