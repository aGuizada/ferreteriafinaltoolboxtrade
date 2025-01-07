<template>
    <div class="main">
        <Panel>
            <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
            </Toast>
            <template #header>
                <div class="panel-header">
                    <h4 class="panel-icon">Inventarios</h4>
                </div>
            </template>
            <template>
                <div>
                    <div class="p-grid p-ai-center p-mb-1">
                        <div class="p-col-12 p-md-3 p-lg-2 p-mb-1 p-mb-md-0">
                            <Button label="Importar" icon="pi pi-plus" @click="abrirModalImportar"
                                class="p-button-success w-full" />
                        </div>
                        <div class="p-grid p-mt-1">
                            <div class="p-col-12 p-md-6 p-lg-4">
                                <span class="p-input-icon-left p-input-icon-right w-full">
                                    <i class="pi pi-search" />
                                    <InputText v-model="buscar" placeholder="Buscar por nombre de producto"
                                        @input="listarInventario(1, buscar, criterio)" class="w-full" />
                                </span>
                            </div>
                        </div>
                        <div class="p-col-12 p-md-4 p-lg-3 p-mb-1 p-mb-md-0">
                            <label class="p-text-bold p-mr-2">ALMACEN</label>
                            <Dropdown v-model="AlmacenSeleccionado" :options="arrayAlmacenes"
                                optionLabel="nombre_almacen" optionValue="id" placeholder="Seleccione"
                                @change="getDatosAlmacen" class="w-full" />
                        </div>
                    </div>
                </div>
                <DataTable :value="arrayInventario" :rows="10" :rowsPerPageOptions="[5, 10, 20]"
                    responsiveLayout="scroll" class="p-datatable-gridlines p-datatable-sm moto-table">
                    <Column v-for="col in columnas" :key="col.field" :field="col.field" :header="col.header">
                    </Column>
                </DataTable>

                <Paginator :rows="10" :totalRecords="pagination.total" @page="onPageChange($event)"
                    :rowsPerPageOptions="[5, 10, 20]" />
            </template>
        </Panel>
        <div v-if="modalImportar">
            <ImportarExcelInventario @cerrar="cerrarModalImportar" />
        </div>
    </div>
</template>
<script>
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import RadioButton from 'primevue/radiobutton';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import Panel from 'primevue/panel';
import Toast from 'primevue/toast';
export default {
    components: {
        Card,
        Button,
        Dropdown,
        RadioButton,
        InputText,
        DataTable,
        Column,
        Paginator,
        Dialog,
        Panel,
        Toast,
    },
    data() {
        return {
            arrayInventario: [],
            arrayAlmacenes: [],
            AlmacenSeleccionado: 1,
            idalmacen: 0,
            tipoSeleccionado: 'lote',
            modalImportar: false,
            pagination: {
                total: 0,
                current_page: 0,
                per_page: 0,
                last_page: 0,
                from: 0,
                to: 0,
            },
            offset: 3,
            criterio: 'nombre',
            buscar: '',
            columnas: []
        }
    },
    methods: {
        abrirModalImportar() {
            this.modalImportar = true;
        },
        cerrarModalImportar() {
            this.modalImportar = false;
            this.listarInventario(1, '', 'nombre');
        },
        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            this.listarInventario(page, buscar, criterio);
        },
        listarInventario(page, buscar, criterio) {
            let me = this;
            let url = '/inventarios/itemLote/' + me.tipoSeleccionado + '?&idAlmacen=' + me.almacenSeleccionado + '&buscar=' + buscar + '&criterio=' + criterio + '&page=' + page;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayInventario = respuesta.inventarios.data;
                me.pagination = respuesta.pagination;
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
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        getDatosAlmacen() {
            if (this.AlmacenSeleccionado !== '') {
                this.almacenSeleccionado = this.AlmacenSeleccionado;
                this.idalmacen = Number(this.AlmacenSeleccionado);
                this.listarInventario(1, '', 'nombre');
            }
        },
        cambiarTipo() {
            this.getDatosAlmacen();
            this.setColumnas();
        },
        onPageChange(event) {
            this.cambiarPagina(event.page + 1, this.buscar, this.criterio);
        },
        setColumnas() {
            if (this.tipoSeleccionado === 'item') {
                this.columnas = [
                    { field: 'nombre_producto', header: 'Producto' },
                    { field: 'unidad_envase', header: 'Unidad X Paq.' },
                    { field: 'saldo_stock_total', header: 'Saldo Stock Total' },
                    { field: 'cantidad', header: 'Cantidad' },
                    { field: 'nombre_almacen', header: 'Almacén' }
                ];
            } else {
                this.columnas = [
                    { field: 'nombre_producto', header: 'Producto' },
                    { field: 'cantidad', header: 'Cantidad' },
                    { field: 'saldo_stock', header: 'Saldo Stock' },
                    { field: 'unidad_envase', header: 'Unid.X.Paq' },
                    { field: 'precio_costo_unid', header: 'Costo Unidad' },
                    { field: 'fecha_ingreso', header: 'Fecha Ingreso' },
                    { field: 'fecha_vencimiento', header: 'Fecha Vencimiento' },
                    { field: 'nombre_almacen', header: 'Almacén' }
                ];

            }
        }
    },
    mounted() {
        this.getDatosAlmacen();
        this.listarInventario(1, '', 'nombre');
        this.selectAlmacen();
        this.setColumnas();
    }
}
</script>

<style scoped>
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
