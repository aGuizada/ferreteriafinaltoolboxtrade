<template>
    <main class="main">
   <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
            </Toast>
        <template >
                <div class="panel-header">
                    <h4 class="panel-icon">Historial De Compras Realizadas </h4>
                </div>
            </template>
        <!-- Ejemplo de tabla Listado -->
        <div class="card" v-if="listado != 0">
            
            <!-- Listado-->
            <template v-if="listado == 1">

                <div class="form-group row">
                    <div class="col-md-6">
                    <div class="input-group">
                        <input 
                            type="text" 
                            v-model="buscar" 
                            @keyup="listarIngreso(1, buscar, criterio,FechaHora)"
                            class="form-control" 
                            placeholder="Buscar..."
                        >
                        <button
  type="button"
  @click="descargarReportePDF"
  class="btn btn-sm btn-primary"
  style="margin-left: 5px;"
>
  <i class="fas fa-file-pdf"></i> Descargar PDF
</button>
                    </div>

                </div>
 
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Acciones</th>
                                <th class="d-none d-md-table-cell">Usuario</th>
                                <th>Proveedor</th>
                                <th class="d-none d-md-table-cell">Tipo Comprobante</th>
                                <th class="d-none d-md-table-cell">Serie Comprobante</th>
                                <th class="d-none d-md-table-cell">Número Comprobante</th>
                                <th>Fecha Hora</th>
                                <th>Total</th>
                                <th class="d-none d-md-table-cell">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ingreso in arrayIngreso" :key="ingreso.id">
                                <td>
                                      <!-- Botón para ver ingreso -->
                                    <button
                                      type="button"
                                      @click="verIngreso(ingreso.id)"
                                      class="btn btn-sm p-button p-component p-button-icon-only"
                                      style="background-color: green; border-color: green; color: white;"
                                    >
                                      <span class="pi pi-eye p-button-icon"></span>
                                      <span class="p-button-label">&nbsp;</span>
                                    </button>
                                
                                    <!-- Botón para imprimir documento -->
                                    <button
                                      type="button"
                                      @click="imprimirDocumento(ingreso.id)"
                                      class="btn btn-sm p-button p-component p-button-icon-only"
                                      style="background-color: blue; border-color: blue; color: white; margin-left: 5px;"
                                    >
                                      <span class="pi pi-print p-button-icon"></span>
                                      <span class="p-button-label">&nbsp;</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="generarReporteCompra(ingreso.id)"
                                        class="btn btn-sm p-button p-component p-button-icon-only"
                                        style="background-color: orange; border-color: orange; color: white; margin-left: 5px;"
                                    >
                                        <span class="pi pi-download p-button-icon"></span>
                                        <span class="p-button-label">&nbsp;</span>
                                    </button>
                                    <template v-if="ingreso.estado == 'Registrado'">
                                        <button type="button" class="btn btn-danger btn-sm"
                                            @click="desactivarIngreso(ingreso.id)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </template>
                                </td>
                                <td class="d-none d-md-table-cell" v-text="ingreso.usuario"></td>
                                <td v-text="ingreso.nombre"></td>
                                <td class="d-none d-md-table-cell" v-text="ingreso.tipo_comprobante"></td>
                                <td class="d-none d-md-table-cell" v-text="ingreso.serie_comprobante"></td>
                                <td class="d-none d-md-table-cell" v-text="ingreso.num_comprobante"></td>
                                <td v-text="ingreso.fecha_hora"></td>
                                <td>
                                    {{ (ingreso.total * parseFloat(monedaCompra[0])).toFixed(2) }} {{
                                        monedaCompra[1] }}

                                </td>
                                <td class="d-none d-md-table-cell" v-text="ingreso.estado"></td>
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

            </template>
            <template v-else-if="listado == 2">
                <div class="card-body">
                    <div class="form-group row border">
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="">Proveedor</label>
                                <p v-text="proveedor"></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo Comprobante</label>
                                <p v-text="tipo_comprobante"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Serie Comprobante</label>
                                <p v-text="serie_comprobante"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Número Comprobante</label>
                                <p v-text="num_comprobante"></p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row border">
                        <div class="table-responsive col-md-12">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Artículo</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayDetalle.length">
                                    <tr v-for="detalle in arrayDetalle" :key="detalle.id">
                                        <td v-text="detalle.articulo">
                                        </td>
                                        <td>
                                            {{ (detalle.precio * parseFloat(monedaCompra[0])).toFixed(2) }} {{
                                                monedaCompra[1] }}

                                        </td>
                                        <td v-text="detalle.cantidad">
                                        </td>
                                        <td>
                                            {{ ((detalle.precio * detalle.cantidad)
                                                * parseFloat(monedaCompra[0])).toFixed(2) }} {{ monedaCompra[1] }}

                                        </td>
                                    </tr>
                                    <tr style="background-color: #CEECF5;">
                                        <td colspan="3" align="right"><strong>Total Parcial:</strong></td>
                                        <td>
                                            {{ ((totalParcial = (total - totalImpuesto))
                                                * parseFloat(monedaCompra[0])).toFixed(2) }} {{ monedaCompra[1] }}

                                        </td>
                                    </tr>

                                    <tr style="background-color: #CEECF5;">
                                        <td colspan="3" align="right"><strong>Total Neto:</strong></td>
                                        <td>
                                            {{ (total * parseFloat(monedaCompra[0])).toFixed(2) }} {{
                                                monedaCompra[1]
                                            }}

                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="4">
                                            No hay articulos agregados
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <button type="button" @click="ocultarDetalle()" class="btn btn-secondary">Cerrar</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div v-if="showModalArticulos">
            <modalagregarproductos @cerrar="cerrarModal" @agregarArticulo="agregarArticuloSeleccionado"
                :idproveedor="idproveedor" :monedaPrincipal="monedaCompra"></modalagregarproductos>
        </div>
        <div v-if="listado == 0" class="mx-3">

            <registrarcompra @cerrar="ocultarDetalle" @listarArticuloProveedor="listarArticuloProveedor"
                @abrirModalArticulos="abrirModal" @listarIngreso="listarIngresosTabla"
                :arrayArticuloSeleccionado="arrayArticuloSeleccionado" :monedaCompra="monedaCompra"></registrarcompra>
        </div>
    </main>
</template>
<script>
import vSelect from 'vue-select';
import Toast from 'primevue/toast';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Paginator from 'primevue/paginator';
import Panel from 'primevue/panel';
export default {
    components: {
        Panel,
        Card,
        DataTable,
        Column,
        Button,
        Dropdown,
        InputText,
        Dialog,
        Paginator
    },
    data() {
        return {
            monedaCompra: [],
            showModalArticulos: false,
            tipoUnidadSeleccionada: "Unidades",
            arrayArticuloSeleccionado: {},
            fechavencimiento: null,

            AlmacenSeleccionado: '',
            arrayAlmacenes: [],
            ingreso_id: 0,
            idproveedor: 0,
            proveedor: '',
            nombre: '',
            tipo_comprobante: 'BOLETA',
            serie_comprobante: '',
            num_comprobante: '',
            impuesto: 0.18,
            total: 0.0,
            totalImpuesto: 0.0,
            totalParcial: 0.0,
            arrayIngreso: [],
            arrayProveedor: [],
            arrayDetalle: [],
            listado: 1,
            modal: 0,
            tituloModal: '',
            tipoAccion: 0,
            errorIngreso: 0,
            errorMostrarMsjIngreso: [],
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
            FechaHora:'',
            criterioA: 'nombre',
            buscarA: '',
            arrayArticulo: [],
            idarticulo: 0,
            codigo: '',
            articulo: '',
            precio: 0,
            cantidad: 1,
        }
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
                resultado = resultado + (this.arrayDetalle[i].precio * this.arrayDetalle[i].cantidad)
            }
            return resultado;
        }
    },
    methods: {
        imprimirDocumento(id) {
            swal({
                title: "Selecciona el tipo de documento",
                text: "¿Qué tipo de documento deseas generar?",
                type: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Nota de Ingreso",
                cancelButtonText: "Boleta",
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    // El usuario seleccionó "Nota de Ingreso"
                    window.open("/ingreso/generar-nota-ingreso/" + id, "_blank");
                } else {
                    // El usuario seleccionó "Boleta"
                    window.open("/ingreso/generar-pdf-boleta/" + id, "_blank");
                }
            });
        },

        generarNotaIngreso(id) {
            window.open("/ingreso/generar-nota-ingreso/" + id, "_blank");
        },
        pdfboleta(id) {
            window.open("/ingreso/generar-pdf-boleta/" + id, "_blank");
        },
        datosConfiguracion() {
            let me = this;
            var url = '/configuracion';

            axios.get(url).then(function (response) {
                var respuesta = response.data;

                me.monedaCompra = [respuesta.configuracionTrabajo.valor_moneda_compra, respuesta.configuracionTrabajo.simbolo_moneda_compra]
                console.log("MostrarCostos: " + me.mostrarCostos);
                console.log("ProveedorEstado: " + me.mostrarProveedores);
                console.log("MostrarSaldosStock: " + me.mostrarSaldosStock);

            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        selectAlmacen() {
            let me = this;
            var url = '/almacen/selectAlmacen';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayAlmacenes = respuesta.almacenes;
                console.log(me.arrayAlmacenes);

            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        atajoButton: function (event) {
            console.log(event.keyCode);
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
        },
        listarIngresosTabla(dato) {
            const page = dato.page;
            const buscar = dato.buscar;
            const criterio = dato.criterio;
            this.listarIngreso(page, buscar, criterio);
        },
        listarIngreso(page, buscar, criterio) {

            let me = this;
            var url = '/ingreso?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayIngreso = respuesta.ingresos.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        selectProveedor(search, loading) {
            let me = this;
            loading(true)
            var url = '/proveedor/selectProveedor?filtro=' + search;
            axios.get(url).then(function (response) {
                //console.log(response);
                let respuesta = response.data;
                q: search
                me.arrayProveedor = respuesta.proveedores;
                loading(false)
            })
                .catch(function (error) {
                    console.log(error);
                });
        },


        buscarArticulo() {
            console.log("BNuscando");
            let me = this;
            var url = '/articulo/buscarArticulo?filtro=' + me.codigo;

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayArticulo = respuesta.articulos;
                if (me.arrayArticulo.length > 0) {
                    me.arrayArticuloSeleccionado = me.arrayArticulo[0];
                    // me.articulo = me.arrayArticulo[0]['nombre'];
                    // me.idarticulo = me.arrayArticulo[0]['id'];
                    console.log("==========");

                    console.log(me.arrayArticulo);
                    console.log("==========");

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

        cambiarPagina(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            //Envia la petición para visualizar la data de esa página
            me.listarIngreso(page, buscar, criterio);
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
            console.log("Almacen seleccionadop");
            console.log(me.AlmacenSeleccionado);
            console.log("Almacen seleccionadop");

            if (me.arrayArticuloSeleccionado.length == 0 || me.cantidad == 0 || me.fechavencimiento == null || me.AlmacenSeleccionado == 0) {
                console.log("Seleccione un producto o la fecha o verifique la cantidads o almacen");
            } else {
                if (me.encuentra(me.arrayArticuloSeleccionado.id)) {
                    swal({
                        type: 'error',
                        title: 'Error...',
                        text: 'Este Artículo ya se encuentra agregado!',
                    })
                }
                else {
                    if (me.tipoUnidadSeleccionada == "Paquetes") {

                        me.arrayDetalle.push({

                            idarticulo: me.arrayArticuloSeleccionado.id,
                            idalmacen: me.AlmacenSeleccionado,
                            codigo: me.arrayArticuloSeleccionado.codigo,
                            articulo: me.arrayArticuloSeleccionado.nombre,
                            precio: me.arrayArticuloSeleccionado.precio_costo_unid,
                            unidad_x_paquete: me.arrayArticuloSeleccionado.unidad_envase,


                            fecha_vencimiento: me.fechavencimiento,
                            cantidad: me.cantidad * me.arrayArticuloSeleccionado.unidad_envase,
                        });
                    } else {


                        me.arrayDetalle.push({
                            idarticulo: me.arrayArticuloSeleccionado.id,
                            idalmacen: me.AlmacenSeleccionado,

                            codigo: me.arrayArticuloSeleccionado.codigo,
                            articulo: me.arrayArticuloSeleccionado.nombre,
                            precio: me.arrayArticuloSeleccionado.precio_costo_unid,
                            unidad_x_paquete: me.arrayArticuloSeleccionado.unidad_envase,

                            fecha_vencimiento: me.fechavencimiento,
                            cantidad: me.cantidad,
                        });
                    }
                    me.arrayArticuloSeleccionado = {};
                    me.codigo = '';
                    me.idarticulo = 0;
                    me.articulo = '';
                    me.cantidad = 1;
                    me.precio = 0;
                    me.fechavencimiento = null;
                }

            }

        },
        agregarArticuloSeleccionado(data = []) {
            let me = this;
            if (me.encuentra(data['id'])) {
                swal({
                    type: 'error',
                    title: 'Error...',
                    text: 'Este Artículo ya se encuentra agregado!',
                })
            } else {
                console.log("==========");
                console.log(data);
                console.log("==========");
                me.arrayArticuloSeleccionado = {
                    codigo: data['codigo'],
                    descripcion: data['descripcion'],
                    fotografia: data['fotografia'],
                    id: data['id'],
                    nombre: data['nombre'],
                    precio_costo_unid: data['precio_costo_unid'],
                    unidad_envase: data['unidad_envase'],
                    precio_costo_paq: data['precio_costo_paq'],
                    vencimiento: data['vencimiento']

                }
                me.codigo = me.arrayArticuloSeleccionado.codigo;
                this.showModalArticulos = false;
            }
        },
        listarArticulo(buscar, criterio) {
            console.log("Listando");
            let me = this;
            var url = '/articulo/listarArticulo?buscar=' + buscar + '&criterio=' + criterio + '&idProveedor=' + this.idproveedor;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayArticulo = respuesta.articulos.data;
                console.log(me.arrayArticulo);
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        guardarInventarios() {
            axios.post('/inventarios/registrar', { inventarios: this.arrayDetalle })
                .then(response => {
                    console.log(response.data);
                })
                .catch(error => {
                    console.error(error);
                });
        },
        registrarIngreso() {
            if (this.validarIngreso()) {
                return;
            }

            let me = this;
            for (let i = 0; i < me.arrayDetalle.length; i++) {
                console.log(me.arrayDetalle[i].idalmacen);
                console.log(me.arrayDetalle[i].idarticulo);
                console.log(me.arrayDetalle[i].fecha_vencimiento);
                console.log(me.arrayDetalle[i].cantidad);
                console.log(me.arrayDetalle[i]);

            }

            axios.post('/ingreso/registrar', {
                'idproveedor': this.idproveedor,
                'tipo_comprobante': this.tipo_comprobante,
                'serie_comprobante': this.serie_comprobante,
                'num_comprobante': this.num_comprobante,
                'impuesto': this.impuesto,
                'total': this.total,
                'data': this.arrayDetalle

            }).then(function (response) {
                if (response.data.id > 0) {
                    me.guardarInventarios();
                    me.listado = 1;
                    me.listarIngreso(1, '', 'num_comprobante');
                    me.idproveedor = 0;
                    me.tipo_comprobante = 'BOLETA';
                    me.serie_comprobante = '';
                    me.num_comprobante = '';
                    me.impuesto = 0.18;
                    me.total = 0.0;
                    me.idarticulo = 0;
                    me.articulo = '';
                    me.cantidad = 1;
                    me.precio = 0;
                    me.arrayDetalle = [];
                } else {
                    swal(
                        'Aviso',
                        response.data.caja_validado,
                        'warning'
                    )
                    return;
                }

            }).catch(function (error) {
                console.log(error);
            });
        },
        validarIngreso() {
            this.errorIngreso = 0;
            this.errorMostrarMsjIngreso = [];

            if (this.idproveedor == 0) this.errorMostrarMsjIngreso.push("Seleccione un Proveedor");
            if (this.tipo_comprobante == 0) this.errorMostrarMsjIngreso.push("Sleccione el Comprobante");
            if (!this.num_comprobante) this.errorMostrarMsjIngreso.push("Ingrese el numero de comprobante");
            if (!this.impuesto) this.errorMostrarMsjIngreso.push("Ingrese el impuesto de compra");
            if (this.arrayDetalle.length <= 0) this.errorMostrarMsjIngreso.push("Ingrese detalles");

            if (this.errorMostrarMsjIngreso.length) this.errorIngreso = 1;

            return this.errorIngreso;
        },
        mostrarDetalle() {
            let me = this;
            me.selectAlmacen();

            me.listado = 0;

            me.idproveedor = 0;
            me.tipo_comprobante = 'BOLETA';
            me.serie_comprobante = '';
            me.num_comprobante = '';
            me.impuesto = 0.18;
            me.total = 0.0;
            me.idarticulo = 0;
            me.articulo = '';
            me.cantidad = 1;
            me.precio = 0;
            me.arrayDetalle = [];
        },
        ocultarDetalle() {
            this.listado = 1;
        },
        verIngreso(id) {
            let me = this;
            me.listado = 2;

            //Obtener datos del ingreso
            var arrayIngresoT = [];
            var url = '/ingreso/obtenerCabecera?id=' + id;

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                arrayIngresoT = respuesta.ingreso;

                me.proveedor = arrayIngresoT[0]['nombre'];
                me.tipo_comprobante = arrayIngresoT[0]['tipo_comprobante'];
                me.serie_comprobante = arrayIngresoT[0]['serie_comprobante'];
                me.num_comprobante = arrayIngresoT[0]['num_comprobante'];
                me.impuesto = arrayIngresoT[0]['impuesto'];
                me.total = arrayIngresoT[0]['total'];
            })
                .catch(function (error) {
                    console.log(error);
                });

            //obtener datos de los detalles
            var url = '/ingreso/obtenerDetalles?id=' + id;

            axios.get(url).then(function (response) {
                console.log(response);
                var respuesta = response.data;
                me.arrayDetalle = respuesta.detalles;

            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        cerrarModal() {
            this.modal = 0;
            this.showModalArticulos = false;
            this.tituloModal = '';
        },
        abrirModal() {
            this.listarArticulo("", "");
            this.arrayArticulo = [];
            this.modal = 1;
            this.showModalArticulos = true;
            this.tituloModal = 'Seleccione los articulos que desee';

        },
        listarArticuloProveedor(dato) {
            this.idproveedor = dato.idproveedor;
        },
        descargarReportePDF() {
        // Open the PDF in a new tab
        window.open('/reporteComprasPDF', '_blank');
    },
    generarReporteCompra(id) {
        swal({
            title: "Selecciona el formato del reporte",
            text: "¿En qué formato deseas generar el reporte?",
            type: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Rollo",
            cancelButtonText: "Carta",
            reverseButtons: true
        }).then((result) => {
            const formato = result.value ? 'roll' : 'letter';
            window.open(`/ingreso/generar-reporte-compra/${id}/${formato}`, '_blank');
        });
    },

        desactivarIngreso(id) {
            swal({
                title: 'Esta seguro de desactivar este ingreso?',
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

                    axios.put('/ingreso/desactivar', {
                        'id': id
                    }).then(function (response) {
                        me.listarIngreso(1, '', 'num_comprobante');
                        swal(
                            'Anulado!',
                            'El ingreso ha sido anulado con éxito.',
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
    mounted() {
        this.datosConfiguracion();
        this.listarIngreso(1, this.buscar, this.criterio);
        window.addEventListener('keydown', this.atajoButton);
    }
}
</script>
<style scoped>
.card-img {
    width: 120px;
    height: auto;
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
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

@media (min-width: 600px) {
    .btnagregar {
        margin-top: 2rem;
    }
}
>>>.p-panel-header {
    padding: 0.75rem;
}

.panel-header {
    display: flex;
    align-items: center;
}

.panel-icon {
    font-size: 2rem;
    padding-left: 10px;
}

.panel-icon {
    font-size: 1.5rem;
    margin: 0;
}
</style>
