<template>
    <div class="main">
        <Panel>
            <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
                </Toast>
                <template #header>
                    <div class="panel-header">
                        
                        <h3>{{ titulo }}</h3>
                    </div>
                </template>
            
                 <template>
                    <div v-if="listado == 1">
                        <Button v-if="listado == 1" icon="pi pi-plus" label="Nuevo"
                            @click="mostrarDetalle('venta', 'cotizacion')" />
              
                        <div class="p-fluid p-formgrid p-grid">
                            
                            <div class="p-field p-col-6 ">
                                <span class="p-float-label">
                                    
                                    <input v-model="buscar" @input="debounceSearch" placeholder="Buscar"
                                        class="form-control" type="text">
                                </span>
                            </div>  
                        </div>
                        <DataTable :value="arrayVenta" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
                            <Column field="id" header="ID"></Column>
                            <Column field="fecha_hora" header="FECHA">
                                <template #body="slotProps">
                                    {{ new Intl.DateTimeFormat('es-ES').format(new Date(slotProps.data.fecha_hora)) }}
                                </template>
                            </Column>
                            <Column field="fecha_hora" header="HORA">
                                <template #body="slotProps">
                                    {{ new Date(slotProps.data.fecha_hora).toLocaleTimeString([], {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) }}
                                </template>
                            </Column>
                            <Column field="nombre" header="CLIENTE"></Column>
                            <Column field="num_documento" header="NIT/CI"></Column>
                            <Column field="impuesto" header="IMPUESTO"></Column>
                            <Column field="total" header="TOTAL"></Column>
                            <Column field="usuario" header="USUARIO"></Column>
                            <Column field="validez" header="EXPIRA EN">
                                <template #body="slotProps">
                                    {{ Math.floor((new Date(slotProps.data.validez) - new Date()) / (1000 * 60 * 60 *
                                        24)) + 1 }} dias
                                </template>
                            </Column>
                            <Column field="nota" header="NOTA/REF"></Column>
                            <Column header="ACCIONES">
                                <template #body="slotProps">
                                    <Button icon="pi pi-eye" class="p-button-sm p-button-success "
                                        @click="abrirModalDetalles(slotProps.data)" />
                                    <Button icon="pi pi-file-pdf" class="p-button-sm p-button-info "
                                        @click="pdfVenta(slotProps.data.id)" />
                                    <Button icon="pi pi-shopping-cart" class="p-button-sm p-button-success "
                                        @click="abrirVenta(slotProps.data.id)" />
                                    <Button icon="pi pi-pencil" class="p-button-sm p-button-warning"
                                        @click="mostrarDetalle('venta', 'editar', slotProps.data)" />
                                    <Button v-if="slotProps.data.condicion" icon="pi pi-trash"
                                        class="p-button-sm p-button-danger "
                                        @click="desactivarCotizacion(slotProps.data.id)" />
                                    <Button v-else icon="pi pi-check" class="p-button-sm p-button-success"
                                        @click="activarCotizacion(slotProps.data.id)" />
                                </template>
                            </Column>
                        </DataTable>
                        <Paginator :rows="10" :totalRecords="pagination.total" @page="onPageChange($event)" />
                    </div>

                    <div v-else-if="listado == 0">
                        <!-- Form for registering/editing cotizacion -->
                        <div class="p-fluid p-formgrid p-grid">
                            <div class="p-field p-col-12 p-md-4">
                                <label for="cliente">Cliente*</label>
                                <Dropdown id="cliente" v-model="clienteSeleccionado" :options="arrayCliente"
                                    optionLabel="num_documento" placeholder="Num de documento..."
                                    @change="getDatosCliente" />
                            </div>
                            <div class="p-field p-col-12 p-md-2">
                                <label for="nitci">NIT/CI</label>
                                <InputText id="nitci" v-model="nitcliente" readonly />
                            </div>
                            <div class="p-field p-col-12 p-md-3">
                                <label for="celular">Celular</label>
                                <InputText id="celular" v-model="telefono" readonly />
                            </div>
                            <div class="p-field p-col-12 p-md-3">
                                <label for="almacen">Almacen*</label>
                                <Dropdown id="almacen" v-model="AlmacenSeleccionado" :options="arrayAlmacenes"
                                    optionLabel="nombre_almacen" optionValue="id" placeholder="Seleccione"
                                    @change="getDatosAlmacen" />
                            </div>

                            <div class="p-field p-col-12 p-md-2">
                                <label for="impuesto">Impuesto*</label>
                                <InputNumber id="impuesto" v-model="impuesto" mode="decimal" :minFractionDigits="2"
                                    :maxFractionDigits="2" />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="codigo">Código del artículo</label>
                                <div class="p-inputgroup">
                                    <Button icon="pi pi-search" @click="abrirModal" />
                                    <InputText id="codigo" v-model="codigo" placeholder="Codigo del artículo"
                                        @keyup="buscarArticulo" />
                                </div>
                            </div>

                            <div class="p-field p-col-12 p-md-4">
                                <label for="nombre_articulo">Nombre del artículo</label>
                                <InputText id="nombre_articulo" v-model="nombre_articulo" readonly />
                            </div>

                            <div class="p-field p-col-12 p-md-2">
                                <label for="stock">Stock</label>
                                <InputText id="stock" v-model="saldo_stock" readonly />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="precio">Precios*</label>
                                <Dropdown id="precio" v-model="precioseleccionado" :options="arrayPrecios"
                                    optionLabel="nombre_precio" optionValue="precio" @change="mostrarSeleccion" />
                            </div>

                            <div class="p-field p-col-12 p-md-2">
                                <label for="precio_unitario">Precio Unitario</label>
                                <InputNumber id="precio_unitario" v-model="precioseleccionado" mode="currency"
                                    currency="BOB" locale="es-BO" :disabled="true" />
                            </div>

                            <div class="p-field p-col-12 p-md-2">
                                <label for="cantidad">Cantidad*</label>
                                <InputNumber id="cantidad" v-model="cantidad" :min="0" :step="1" />
                            </div>

                            <div class="p-field p-col-12 p-md-2">
                                <label for="descuento">Descuento</label>
                                <InputNumber id="descuento" v-model="descuento" mode="decimal" :minFractionDigits="2"
                                    :maxFractionDigits="2" />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="prec_total">Precio Total</label>
                                <InputNumber id="prec_total" v-model="prectotal" mode="currency" currency="BOB"
                                    locale="es-BO" :readonly="true" />
                            </div>

                            <div class="p-field p-col-12 p-md-3 p-d-flex p-ai-end">
                                <Button label="Añadir item" icon="pi pi-plus" @click="agregarDetalle" />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="dias_validez">Días de validez</label>
                                <InputNumber id="dias_validez" v-model="dias_validez" :min="0" :step="1" />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="tiempo_entrega">Tiempo de entrega</label>
                                <InputText id="tiempo_entrega" v-model="tiempo_entrega" />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="lugar_entrega">Lugar de entrega</label>
                                <InputText id="lugar_entrega" v-model="lugar_entrega" />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="forma_pago">Forma de pago</label>
                                <InputText id="forma_pago" v-model="forma_pago" />
                            </div>

                            <div class="p-field p-col-12">
                                <label for="nota">Nota</label>
                                <Textarea id="nota" v-model="nota" autoResize rows="3" />
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="imprimir">Imprimir</label>
                                <Dropdown id="imprimir" v-model="imprimir" :options="options_print" optionLabel="label"
                                    optionValue="value" placeholder="Seleccione" />
                            </div>
                        </div>

                        <!-- Table for added items -->
                        <DataTable :value="arrayDetalle">
                            <Column header="Opciones">
                                <template #body="slotProps">
                                    <Button icon="pi pi-trash" class="p-button-rounded p-button-danger p-button-text"
                                        @click="eliminarDetalle(slotProps.index)" />
                                </template>
                            </Column>
                            <Column field="codigo" header="Codigo"></Column>
                            <Column field="nombre_articulo" header="Artículo"></Column>
                            <Column field="precioseleccionado" header="Precio/U"></Column>
                            <Column field="unidad_envase" header="Unid/Paq"></Column>
                            <Column field="cantidad" header="Paquetes"></Column>
                            <Column field="prectotal" header="Total"></Column>
                        </DataTable>

                        <div class="p-d-flex p-jc-end">
                            <Button label="Cerrar" icon="pi pi-times" class="p-button-secondary p-mr-2"
                                @click="ocultarDetalle" />
                            <Button v-if="idcotizacionv !== ''" label="Editar Cotización" icon="pi pi-pencil"
                                @click="editarCotizacion" />
                            <Button v-else label="Registrar Cotización" icon="pi pi-check"
                                @click="registrarCotizacion" />
                        </div>
                    </div>
                </template>
            
        </Panel>

        <!-- Modal for selecting products -->
        <Dialog :visible="modal" :containerStyle="{ width: '700px' }" :modal="true" :closable="false">
            <template #header>
                <h3>{{ tituloModal }}</h3>
            </template>
            <DataTable :value="arrayArticulo" :paginator="true" :rows="10">
                <Column header="Opciones">
                    <template #body="slotProps">
                        <Button icon="pi pi-check" class="p-button-rounded p-button-success p-button-text"
                            @click="agregarDetalleModal(slotProps.data)" />
                    </template>
                </Column>
                <Column field="codigo" header="Código"></Column>
                <Column field="nombre" header="Nombre"></Column>
                <Column field="nombre_categoria" header="Categoría"></Column>
                <Column field="precio_venta" header="Precio Venta"></Column>
                <Column field="saldo_stock" header="Stock"></Column>
                <Column field="condicion" header="Estado">
                    <template #body="slotProps">
                        <Tag :severity="slotProps.data.condicion ? 'success' : 'danger'"
                            :value="slotProps.data.condicion ? 'Activo' : 'Desactivado'" />
                    </template>
                </Column>
            </DataTable>
            <template #footer>
                <Button label="Cerrar" icon="pi pi-times" @click="cerrarModal" class="p-button-text" />
            </template>
        </Dialog>

        <detallecotizacionventa v-if="showModalDetalle" @cerrar="cerrarModalDetalles"
            @abrirVenta="abrirFormularioCotizacion" :arrayCotizacionSeleccionado="arrayCotizacionSeleccionado"
            :arrayCotizacionVentDet="arrayCotizacionVentDet" />

        <registrarventa v-if="showRegistrarVenta" :arrayDetalleCotizacion="arrayDetallesAComprar"
            :arrayCotizacionSeleccionado="arrayCotizacionSeleccionado" @cerrar="cerrarFormularioVenta" />
    </div>
</template>

<script>
import vSelect from 'vue-select';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Paginator from 'primevue/paginator';
import Tag from 'primevue/tag';
import InputNumber from "primevue/inputnumber";
import Panel from 'primevue/panel';
import Toast from 'primevue/toast';
export default {
    components: {
        vSelect,
        Card,
        Button,
        DataTable,
        Column,
        InputText,
        Dropdown,
        Dialog,
        Paginator,
        InputNumber,
        Tag,
        Panel,
        Toast,
    },
    data() {
        return {
            //-----
            timeoutId: null,
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
        listarCotizacion(page, buscar) {
            let me = this;
            var url = '/cotizacionventa?page=' + page + '&buscar=' + buscar;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayVenta = respuesta.cotizacion_venta.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        debounceSearch() {
            clearTimeout(this.timeoutId);
            this.timeoutId = setTimeout(() => {
                this.listarCotizacion(1, this.buscar);
            }, 200); // Espera 300ms después de que el usuario deja de escribir
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

                        });

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
                console.log("cotizacion_Ventas_Registrado");
                me.imprimirTicket(response.data.id);

                if (response.data.id > 0) {
                    console.log("cotizacionRegistrado" + me.listado);

                    me.listado = 1;
                    me.listarCotizacion(1, '', '');
                    me.idproveedor = 0;
                    me.impuesto = 0.18;
                    me.total = 0.0;
                    me.idarticulo = 0;
                    me.articulo = '';
                    me.cantidad = 0;
                    me.precio = 0;
                    me.stock = 0;
                    me.codigo = '';
                    me.descuento = 0;
                    me.arrayDetalle = [];
                } else {
                    if (response.data.valorMaximo) {
                        swal(
                            'Aviso',
                            'El valor de descuento no puede exceder el ' + response.data.valorMaximo,
                            'warning'
                        );
                        return;
                    } else {
                        swal(
                            'Aviso',
                            response.data.caja_validado,
                            'warning'
                        );
                        return;
                    }
                }
            }).catch(function (error) {
                console.log(error);
            });
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
<style scoped>
>>> .p-panel-header {
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

