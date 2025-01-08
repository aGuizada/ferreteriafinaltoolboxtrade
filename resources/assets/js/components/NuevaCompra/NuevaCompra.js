import useVuelidate from '@vuelidate/core';
import { required, minValue } from '@vuelidate/validators';

import Button from 'primevue/button';
import Panel from 'primevue/panel';
import InputText from 'primevue/inputtext';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Card from 'primevue/card';
import Dropdown from 'primevue/dropdown';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import AutoComplete from 'primevue/autocomplete';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import Toast from 'primevue/toast';
import SplitButton from 'primevue/splitbutton';
import Dialog from 'primevue/dialog';
import Menu from 'primevue/menu';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';

export default {
    setup () {
        return { 
            v$: useVuelidate() 
        }
    },

    data() {
        return {

            activeIndex: 0,
            submitted: false,
            form: {
                proveedorSeleccionado: null, 
                tipo_comprobante: null,
                serie_comprobante: null,
                num_comprobante: null
            },
            steps: [
                { label: 'Paso 1' },
                { label: 'Paso 2' },
            ],
            lista_comprobantes: [
                {nombre: 'Recibo', id: 1},
                {nombre: 'Nota de Ingreso', id: 2},
            ],

            form_cuotas: {
                num_cuotas: null,
                frecuencia_pagos: null,
                cuota_inicial: null,
                tipoPagoCuotaSeleccionado: null,
            },
            lista_tipo_pago_cuotas: [
                {nombre: 'Efectivo'},
                {nombre: 'Tarjeta'},
                {nombre: 'QR'}
            ],

            codigo: '',
            idproveedor: null,

            buscadorArticulos: null,
            array_proveedores: [],
            loading: false,

            array_articulos_proveedor: [],
            array_articulos_seleccionados: [],
            array_articulos_completo: [],

            minDate: null,

            expandedRows: [],
            editingRows: [],

            tipoCompra: null,
            lista_tipo_compra: [
                {nombre: 'Contado', id: 1},
                {nombre: 'Credito', id: 2},
            ],
            array_almacenes: [],
            almacenSeleccionado: null,
            //total: null,
            nombreUsuario: null,
            usuario_actual_id: null,
            saldoTotalCompra: 0,
            displayConfirmation: false,
            displayCompraCredito: false,
            array_precios: [],
            objeto_newData: null,
            descuentoGlobal: 0,
            array_cuotas_calculadas: [],

            items: [
                {
                    label: 'Extras',
                    items: [{
                        label: 'Expandir todo',
                        icon: 'pi pi-sort-down',
                        command: () => {
                            this.expandAll();
                        }
                    },
                    {
                        label: 'Comprimir todo',
                        icon: 'pi pi-sort-up',
                        command: () => {
                            this.collapseAll();
                        }
                    }
                ]},
            ]
        }
    },

    validations() {
        return {
            form: {
                proveedorSeleccionado: { required },
                tipo_comprobante: { required },
            },

            tipoCompra: {
                required
            },
            almacenSeleccionado: {
                required
            },

            form_cuotas : {
                num_cuotas: {
                    required,
                    minValueValue: minValue(1),
                },
                frecuencia_pagos: {
                    required,
                    minValueValue: minValue(1),
                },
                cuota_inicial: {
                    required,
                    minValueValue: minValue(1),
                },
                tipoPagoCuotaSeleccionado: {
                    required
                },
            }
        }
    },

    components: {
        Button,
        Panel,
        InputText,
        TabView,
        TabPanel,
        Card,
        Dropdown,
        DataTable,
        Column,
        ColumnGroup,
        AutoComplete,
        InputNumber,
        Calendar,
        Toast,
        SplitButton,
        Dialog,
        Menu,
        Badge,
        Tag,
    },

    computed: {
        isMobile() {
            return window.innerWidth <= 576;
        },

        verificarCompraContado() {
            return this.tipoCompra ? this.tipoCompra.id == 2: false;
        },
    },

    methods: {

        verificarCantidad(data) {
            return data.unidades <= 0;
        },

        verificarDescuento(data) {
            return data.descuento < 0 || data.descuento > 100;
        },

        verificarFechaVencimiento(data) {
            return false; 
        },

        calcularSaldoTotalCompra() {
            const totalSinDescuento = this.array_articulos_completo.reduce((total, articulo) => {
                return total + (articulo.subtotal || 0);
            }, 0);
            const descuento = (totalSinDescuento * this.descuentoGlobal) / 100;
            this.saldoTotalCompra = (totalSinDescuento - descuento).toFixed(2);
        },

        updateSubtotal(articulo) {
            const cantidad = articulo.unidades;
            if (cantidad <= 0) {
                articulo.subtotal = 0;
                return;
            }

            const precio = articulo.esPaquetesCantidad ? articulo.precio_costo_paq : articulo.precio_costo_unid;
            const descuento = (articulo.descuento / 100);
            const precioDescontado = precio * (1 - descuento);
            articulo.subtotal = cantidad * precioDescontado;
            this.updateUnidadesTotales(articulo);
            this.$forceUpdate();
        },

        updateUnidadesTotales(articulo) {
            const cantidad = articulo.unidades;
            const bonificacion = articulo.bonificacion;
            const unidadesBonificacion = articulo.esPaquetesBonificacion ? bonificacion * articulo.unidad_envase : bonificacion;
            articulo.unidadesTotales = (articulo.esPaquetesCantidad ? cantidad * articulo.unidad_envase : cantidad) + unidadesBonificacion;
        },

        calculateSubtotal(articulo) {
            const cantidad = articulo.unidades;
            const precio = articulo.esPaquetesCantidad ? articulo.precio_costo_paq : articulo.precio_costo_unid;
            const descuento = (articulo.descuento / 100);
            const precioDescontado = precio * (1 - descuento);
            return cantidad * precioDescontado;
        },

        toggleUnidadesPaquetesCantidad(articulo) {
            articulo.esPaquetesCantidad = !articulo.esPaquetesCantidad;
            this.updateSubtotal(articulo);
        },

        toggleUnidadesPaquetesBonificacion(articulo) {
            articulo.esPaquetesBonificacion = !articulo.esPaquetesBonificacion;
            this.updateUnidadesTotales(articulo);
        },

        async registrarCompra() {

            this.submitted = true;
            const result = await this.validarPaginaActual();

            if (this.array_articulos_completo.length == 0) {
                this.$toast.add({severity:'warn', summary: 'Sin artículos', detail: 'Lista de articulos vacía', life: 3000});
                return;
            }

            if (!result) {
                return;
            }

            const articulosSinFechaVencimiento = this.array_articulos_completo.filter(
                articulo => this.verificarFechaVencimiento(articulo)
            );

            const articulosConCantidadInvalida = this.array_articulos_completo.filter(
                articulo => this.verificarCantidad(articulo)
            );

            const articulosConDescuentoInvalido = this.array_articulos_completo.filter(
                articulo => this.verificarDescuento(articulo)
            );

            if (articulosSinFechaVencimiento.length > 0) {
                this.$toast.add({
                    severity:'error',
                    summary: 'Error de validación',
                    detail: 'Hay artículos sin fecha de vencimiento',
                    life: 3000
                });
                return;
            }

            if (articulosConCantidadInvalida.length > 0) {
                this.$toast.add({
                    severity:'error',
                    summary: 'Error de validación',
                    detail: 'Hay artículos con cantidad inválida',
                    life: 3000
                });
                return;
            }

            if (articulosConDescuentoInvalido.length > 0) {
                this.$toast.add({
                    severity:'error',
                    summary: 'Error de validación',
                    detail: 'Hay artículos con descuento inválido',
                    life: 3000
                });
                return;
            }

            if (this.tipoCompra.id == 2) {
                if (this.array_cuotas_calculadas.length === 0) {
                    this.$toast.add({severity:'warn', summary: 'Sin cuotas', detail: 'Lista de cuotas vacía', life: 3000});
                    return;
                }
            }

            try {
                this.isLoading = true;

                const compraResponse = await axios.post('/ingreso/registrarIngreso', {
                    form: this.form,
                    usuario_actual_id: this.usuario_actual_id,
                    saldoTotalCompra: this.saldoTotalCompra,
                    tipoCompra: this.tipoCompra,
                    almacenSeleccionado: this.almacenSeleccionado,
                    array_articulos_completo: this.array_articulos_completo,
                    descuento_global: this.descuentoGlobal,
                    form_cuotas: this.form_cuotas,
                    cuotaData: this.array_cuotas_calculadas,
                    estado: (this.tipoCompra.id === 2)? 'Pendiente': 'Pagado',
                });

                if (compraResponse.data.status === 'success') {
                    const inventarios = this.prepararDatosInventario();
                    const inventarioResponse = await axios.post('/inventarios/registrarInventario', {
                        inventarios: inventarios
                    });

                    try {
                        if (inventarioResponse.data.status === 'success') {
                            this.$toast.add({severity:'success', summary: 'Éxito', detail: 'Compra registrada e inventario actualizado', life: 5000});

                            this.activeIndex = 0;
                            this.array_articulos_completo = [];
                            this.array_articulos_seleccionados = [];
                            this.array_articulos_proveedor = [];
                            this.form.tipo_comprobante = null;
                            this.form.serie_comprobante = null;
                            this.form.num_comprobante = null;
                            this.form.proveedorSeleccionado = null;
                            this.almacenSeleccionado = null;
                            this.tipoCompra = null;
                            this.saldoTotalCompra = 0;
                            this.descuentoGlobal = 0;

                            this.closeComprasCredito();
                        }
                    } catch(error) {
                        let errorInventario = 'Error al actualizar inventario';
                        this.$toast.add({severity:'error', summary: 'Error', detail: errorInventario, life: 3000});
                    }
                }
            } catch (error) {
                let errorMessage = 'Error al registrar la compra';
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
                this.$toast.add({severity:'error', summary: 'Error', detail: errorMessage, life: 3000});
            } finally {
                this.isLoading = false;
            }
        },

        generarCuotas() {
            if (this.validarCompraCredito()) {
                const numCuotas = this.form_cuotas.num_cuotas;
                const frecuenciaPagos = this.form_cuotas.frecuencia_pagos;
                const montoTotal = this.saldoTotalCompra;
                const cuotaInicial = this.form_cuotas.cuota_inicial;
                
                const montoRestante = montoTotal - cuotaInicial;
                const montoPorCuota = montoRestante / numCuotas;

                let fechaActual = new Date();
                let saldoRestante = montoTotal;

                this.array_cuotas_calculadas = [];

                this.array_cuotas_calculadas.push({
                    id: 0,
                    fecha_pago: fechaActual.toISOString().split('T')[0],
                    precio_cuota: cuotaInicial.toFixed(2),
                    total_cancelado: cuotaInicial.toFixed(2),
                    saldo_restante: (saldoRestante - cuotaInicial).toFixed(2),
                    fecha_cancelado: fechaActual.toISOString().split('T')[0],
                    estado: 'Cuota Inicial'
                });

                saldoRestante -= cuotaInicial;

                for (let i = 1; i <= numCuotas; i++) {
                    let diasRestantes = frecuenciaPagos;
                    do {
                        fechaActual.setDate(fechaActual.getDate() + 1);
                        if (fechaActual.getDay() !== 0) { // 0 es domingo
                            diasRestantes--;
                        }
                    } while (diasRestantes > 0);

                    saldoRestante -= montoPorCuota;

                    this.array_cuotas_calculadas.push({
                        id: i,
                        fecha_pago: fechaActual.toISOString().split('T')[0],
                        precio_cuota: montoPorCuota.toFixed(2),
                        total_cancelado: '0.00',
                        saldo_restante: saldoRestante > 0 ? saldoRestante.toFixed(2) : '0.00',
                        fecha_cancelado: '',
                        estado: 'Pendiente'
                    });
                }
            }
        },

        validarCompraCredito() {
            this.submitted = true;
            if (this.v$.form_cuotas.$invalid) {
                return false;
            }
            return true;
        },

        async openComprasCredito() {

            this.submitted = true;
            const result = await this.validarPaginaActual();

            if (this.array_articulos_completo.length == 0) {
                this.$toast.add({severity:'warn', summary: 'Sin artículos', detail: 'Lista de articulos vacía', life: 3000});
                return;
            }

            if (!result) {
                return;
            }

            /*const articulos_invalidos = this.array_articulos_completo.filter(
                articulo => !articulo.fecha_vencimiento || articulo.unidades <= 0
            );

            if (articulos_invalidos.length > 0) {
                this.$toast.add({
                severity:'error',
                summary: 'Error de validación',
                detail: 'Hay artículos sin fecha de vencimiento o con cantidad inválida',
                life: 3000
                });
                return;
            }*/

            const articulosSinFechaVencimiento = this.array_articulos_completo.filter(
                articulo => this.verificarFechaVencimiento(articulo)
            );

            const articulosConCantidadInvalida = this.array_articulos_completo.filter(
                articulo => this.verificarCantidad(articulo)
            );

            const articulosConDescuentoInvalido = this.array_articulos_completo.filter(
                articulo => this.verificarDescuento(articulo)
            );

            if (articulosSinFechaVencimiento.length > 0) {
                this.$toast.add({
                    severity:'error',
                    summary: 'Error de validación',
                    detail: 'Hay artículos sin fecha de vencimiento',
                    life: 3000
                });
                return;
            }

            if (articulosConCantidadInvalida.length > 0) {
                this.$toast.add({
                    severity:'error',
                    summary: 'Error de validación',
                    detail: 'Hay artículos con cantidad inválida',
                    life: 3000
                });
                return;
            }

            if (articulosConDescuentoInvalido.length > 0) {
                this.$toast.add({
                    severity:'error',
                    summary: 'Error de validación',
                    detail: 'Hay artículos con descuento inválido',
                    life: 3000
                });
                return;
            }

            this.displayCompraCredito = true;
        },

        closeComprasCredito() {
            this.form_cuotas.num_cuotas = 0;
            this.form_cuotas.frecuencia_pagos = 0;
            this.form_cuotas.cuota_inicial = 0;
            this.form_cuotas.tipoPagoCuotaSeleccionado = null;
            this.array_cuotas_calculadas = [];

            this.displayCompraCredito = false;
        },

        prepararDatosInventario() {
            return this.array_articulos_completo.map(articulo => ({
                idalmacen: this.almacenSeleccionado.id,
                idarticulo: articulo.id,
                //fecha_vencimiento: articulo.vencimiento || '2099-01-01',
                fecha_vencimiento: articulo.fecha_vencimiento,
                cantidad: articulo.unidadesTotales
            }));
        },

        actualizarLista() {
            let articulosActualizados = this.array_articulos_seleccionados.map(articulo => {
                let articuloExistente = this.array_articulos_completo.find(a => a.id === articulo.id);
                
                if (articuloExistente) {
                    return {
                        ...articuloExistente,
                        precio_costo_unid: articulo.precio_costo_unid,
                        precio_costo_paq: articulo.precio_costo_paq,
                        nombre: articulo.nombre,
                    };
                } else {
                    return {
                        ...articulo,
                        fecha_vencimiento: null,
                        unidadesTotales: 0,
                        vencimiento: null,
                        unidades: 0,
                        bonificacion: 0,
                        descuento: 0,
                        subtotal: 0,
                        esPaquetesCantidad: false,
                        esPaquetesBonificacion: false,
                    };
                }
            });

            this.array_articulos_completo = articulosActualizados;
        },

        onRowEditSave(event) {
            //let { newData, index } = event;
            //this.$set(this.array_articulos_seleccionados, index, newData);
            this.listarPrecios();
            this.displayConfirmation = true;
            this.objeto_newData = event;
        },

        closeConfirmation() {
            this.displayConfirmation = false;
        },

        async actualizarCostosArticulo() {

            try {
                let newData = this.objeto_newData.newData;
                let index = this.objeto_newData.index;

                const compraResponse = await axios.post('/articulo/actualizarPrecios', {
                    newData
                });

                if (compraResponse.data.status === 'success') {
                    this.$set(this.array_articulos_seleccionados, index, newData);
                    if (this.buscadorArticulos == null) {
                        this.listarArticulo('');
                    } else {
                        this.listarArticulo(this.buscadorArticulos);
                    }
                    this.$toast.add({severity:'success', summary: 'Actualizado', detail: compraResponse.data.message, life: 3000});
                }

                this.objeto_newData = null;
            }
            catch (error) {
                let errorMessage = 'Error al actualizar los costos del articulo';
                this.$toast.add({severity:'error', summary: 'Error', detail: errorMessage, life: 3000});
            }

            this.displayConfirmation = false;
        },

        onRowExpand(event) {
            this.$nextTick(() => {
                this.updateSubtotal(event.data);
            });
            this.$toast.add({severity: 'info', summary: 'Información adicional', detail: event.data.name, life: 3000});
        },

        onRowCollapse(event) {
            this.$toast.add({severity: 'info', summary: 'Información comprimida', detail: event.data.name, life: 3000});
        },

        vaciarListaSeleccionados() {
            this.array_articulos_seleccionados.splice(0, this.array_articulos_seleccionados.length);
        },

        eliminarArticuloListaCompleta(articulo) {
            if (articulo && articulo.id) {
                this.array_articulos_completo = this.array_articulos_completo.filter(a => a.id !== articulo.id);
                this.array_articulos_seleccionados = this.array_articulos_seleccionados.filter(a => a.id !== articulo.id);
            }
        },

        eliminarArticuloSeleccionado(articulo) {
            if (articulo && articulo.id) {
                this.array_articulos_seleccionados = this.array_articulos_seleccionados.filter(a => a.id !== articulo.id);
                this.array_articulos_completo = this.array_articulos_completo.filter(a => a.id !== articulo.id);
            }
        },

        async validarPaginaActual() {

            this.submitted = true;
            if (this.activeIndex === 0) {
                this.v$.form.$touch();
                this.submitted = this.v$.form.$invalid;
                return !this.v$.form.$invalid;
            } else if (this.activeIndex === 1) {
                this.v$.tipoCompra.$touch();
                this.v$.almacenSeleccionado.$touch();
                this.submitted = (this.v$.tipoCompra.$invalid || this.v$.almacenSeleccionado.$invalid)
                return !(this.v$.tipoCompra.$invalid || this.v$.almacenSeleccionado.$invalid);
            }
        },

        async nextStep() {
            const result = await this.validarPaginaActual();

            if (!result) {
                return;
            }

            if (this.activeIndex < this.steps.length - 1) {
                if (this.activeIndex === 0) {
                    this.actualizarLista();
                }
                this.activeIndex++;
            }
        },

        prevStep() {
            //this.activeIndex -= 1;
            if (this.activeIndex > 0) {
                this.activeIndex--;
            }
        },

        listarArticulo(buscar) {
            let me = this;
            var url = '/articulo/buscadorGlobal?buscar=' + buscar + '&idProveedor=' + this.idproveedor;
            axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    //me.array_articulos_proveedor = respuesta.articulos.data;
                    me.array_articulos_proveedor = respuesta.articulos.data.map(articulo => ({
                        ...articulo,
                        precio_costo_unid: Number(articulo.precio_costo_unid),
                        precio_costo_paq: Number(articulo.precio_costo_paq),
                    }));
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        listarDatosUsuario() {
            axios.get('/user-info')
                .then(response => {
                    const userData = response.data.user;
                    this.usuario_actual_id = userData.iduse;
                    this.extraerDatosUsuario(this.usuario_actual_id);
                })
                .catch(error => {
                    console.error(error);
                });
        },

        extraerDatosUsuario(id_persona) {
            let me = this;
            var url = '/user/editarpersona?id=' + id_persona;

            axios.get(url).then(function (response) {
                var respuesta = response.data;

                me.nombreUsuario = respuesta.persona.nombre;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        buscarAlmacen() {
            let me = this;
            this.loading = true;

            var url = `/almacen/buscador?page=${1}&buscar=${me.almacenSeleccionado}`;
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.array_almacenes = respuesta.almacenes.data;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
        },

        selectProveedor(event) {
            let me = this;

            if (!event.query.trim().length) {
                var url = `/proveedor?page=${1}&buscar=${''}&criterio=${'todos'}&por_pagina=${3}`;
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.array_proveedores = respuesta.personas.data;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
            }
            else
            {
                this.loading = true;

                var url = `/proveedor?page=${1}&buscar=${me.form.proveedorSeleccionado}&criterio=${'todos'}&por_pagina=${3}`;
                axios.get(url).then(function (response) {
                        var respuesta = response.data;
                        me.array_proveedores = respuesta.personas.data;
                        me.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loading = false;
                    });
            }
        },

        listarPrecios() {
            let me = this;
            var url = '/precios';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.array_precios = respuesta.precio.data;
            }).catch(function (error) {
                console.log(error);
            });
        },

        expandAll() {
            this.expandedRows = this.array_articulos_completo.filter(p => p.id);
            this.$toast.add({severity: 'success', summary: 'Todas las filas expandidas', life: 4000});
        },

        collapseAll() {
            this.expandedRows = null;
            this.$toast.add({severity: 'success', summary: 'Todas las filas comprimidas', life: 4000});
        },

        toggle(event) {
            this.$refs.menu.toggle(event);
        },
    },

    watch: {
        'form.proveedorSeleccionado.id': {
            handler(newVal) {
                if (newVal) {
                    this.idproveedor = newVal;
                    this.listarArticulo('');
                }
            }
        },

        buscadorArticulos(newVal) {
            if (newVal) {
                this.listarArticulo(this.buscadorArticulos);
            }
        },

        array_articulos_completo: {
            handler() {
                this.calcularSaldoTotalCompra();
            },
            deep: true
        },

        descuentoGlobal: {
            handler() {
                this.calcularSaldoTotalCompra();
            }
        },
    },

    created() {
        this.minDate = new Date();
    },

    mounted() {
        this.listarDatosUsuario();
    },

    beforeDestroy() {
        
    },
}