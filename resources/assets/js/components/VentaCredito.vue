<template>
    <div class="main">

        <Card style="padding-top:15px">

            <template #header>
                <div class="panel-header">
                    <i class="pi pi-shopping-cart panel-icon"></i>
                    <h4 class="panel-icon">Ventas Credito</h4>
                </div>
            </template>
            <template #content>
                <div class="p-grid">
                    <div class="p-col-4">
                        <div class="p-inputgroup">
                            <Dropdown v-model="criterio" :options="criterioOptions" optionLabel="label"
                                optionValue="value" class="p-col-4-sm" />
                            <InputText class="p-col-4-sm" v-model="buscar" placeholder="Texto a buscar"
                                @keyup.enter="obtenerCreditos(1, buscar, criterio, filtroAvanzado)" />
                            <Button class="p-col-4-sm" icon="pi pi-search"
                                @click="obtenerCreditos(1, buscar, criterio, filtroAvanzado)" label="Buscar" />
                        </div>
                    </div>
                    <div class="p-col-2">
                        <Dropdown v-model="filtroAvanzado" :options="filtroAvanzadoOptions" optionLabel="label"
                            optionValue="value" @change="obtenerCreditos(1, buscar, criterio, filtroAvanzado)" />
                    </div>
            
                </div>

                <DataTable :value="arrayCreditos" class="p-datatable-gridlines p-datatable-sm"
                    responsiveLayout="scroll">
                    <Column header="Acciones">
                        <template #body="slotProps">
                            <Button v-if="slotProps.data.estado != 'Completado'" icon="pi pi-plus"
                                class="p-button-success p-button-sm" @click="abrirDetalle(slotProps.data)" />
                            <Button v-else icon="pi pi-eye" class="p-button-secondary p-button-sm"
                                @click="abrirDetalle(slotProps.data)" />
                            <Button icon="pi pi-file-pdf" class="p-button-danger p-button-sm"
                                @click="generarReciboGeneral(slotProps.data.id)" />
                        </template>
                    </Column>
                    <Column field="nombre_cliente" header="Cliente" />
                    <Column field="nombre_vendedor" header="Vendedor" />
                    <Column header="Total de la venta">
                        <template #body="slotProps">
                            {{ (slotProps.data.totalVenta * monedaVenta[0]).toFixed(2) }} {{ monedaVenta[1] }}
                        </template>
                    </Column>
                    <Column header="Monto pendiente">
                        <template #body="slotProps">
                            {{ (slotProps.data.total * monedaVenta[0]).toFixed(2) }} {{ monedaVenta[1] }}
                        </template>
                    </Column>
                    <Column header="Pagadas">
                        <template #body="slotProps">
                            {{ calcularCuotasPagadas(slotProps.data.totalVenta, slotProps.data.total,
                                slotProps.data.numero_cuotas) }}/{{ slotProps.data.numero_cuotas }}
                        </template>
                    </Column>
                    <Column header="Próximo pago">
                        <template #body="slotProps">
                            {{ slotProps.data.estado == "Completado" ? "Sin Próximos Pagos" :
                                formatFecha(slotProps.data.proximo_pago) }}
                        </template>
                    </Column>
                    <Column header="Estado">
                        <template #body="slotProps">
                            <i class="pi pi-circle-on"
                                :style="{ color: getColorForEstado(slotProps.data.estado, slotProps.data.proximo_pago) }"></i>
                            {{ slotProps.data.estado === "Completado" ? 'Completado' : (new
                                Date(slotProps.data.proximo_pago) < new Date() ? 'Atrasado' : slotProps.data.estado) }}
                                </template>
                    </Column>

                </DataTable>

                <Paginator :rows="pagination.per_page" :totalRecords="pagination.total"
                    :first="(pagination.current_page - 1) * pagination.per_page" @page="cambiarPagina($event)" />

            </template>
        </Card>


        <Dialog :visible.sync="modalDetalle" :modal="true" :containerStyle="{ width: '70vw' }" :closable="true"
            :dismissableMask="true" :closeOnEscape="true" :header="'Detalle de venta a crédito'"
            @hide="cerrarModalDetalle">
            <div v-if="arraySeleccionado">
                <div class="p-grid">
                    <div class="p-col-3">Nombre del cliente:</div>
                    <div class="p-col-9">{{ arraySeleccionado.nombre_cliente }}</div>
                    <div class="p-col-3">Nombre del vendedor:</div>
                    <div class="p-col-9">{{ arraySeleccionado.nombre_vendedor }}</div>
                    <div class="p-col-3">Comprobante:</div>
                    <div class="p-col-9">{{ arraySeleccionado.tipo_comprobante }} - {{ arraySeleccionado.num_comprobante
                        }}
                    </div>
                    <div class="p-col-3">Próximo pago:</div>
                    <div class="p-col-9">{{ formatFecha(arraySeleccionado.proximo_pago) }}</div>
                    <div class="p-col-3">Total de la venta:</div>
                    <div class="p-col-3">{{ (arraySeleccionado.totalVenta * monedaVenta[0]).toFixed(2) }} {{
                        monedaVenta[1] }}
                    </div>
                    <div class="p-col-3">Monto pendiente:</div>
                    <div class="p-col-3">{{ (arraySeleccionado.total * monedaVenta[0]).toFixed(2) }} {{ monedaVenta[1]
                        }}</div>
                </div>

                <DataTable :value="arrayCuotas" class="p-datatable-gridlines p-datatable-sm" :paginator="true"
                    :rows="10" :rowsPerPageOptions="[10, 20, 50]" responsiveLayout="scroll">
                    <Column field="index" header="#" />
                    <Column field="fecha_pago" header="Fecha Pago">
                        <template #body="slotProps">
                            {{ new Date(slotProps.data.fecha_pago).toLocaleDateString('es-ES') }}
                        </template>
                    </Column>
                    <Column field="precio_cuota" header="Precio Cuota">
                        <template #body="slotProps">
                            {{ (slotProps.data.precio_cuota * monedaVenta[0]).toFixed(2) }} {{ monedaVenta[1] }}
                        </template>
                    </Column>
                    <Column field="recargo" header="Recargo">
                        <template #body="slotProps">
                            <span :class="{ 'text-danger': slotProps.data.recargo > 0 }">
                                {{ (slotProps.data.recargo * monedaVenta[0]).toFixed(2) }} {{ monedaVenta[1] }}
                            </span>
                        </template>
                    </Column>
                    <Column field="total_a_pagar" header="Total a Pagar">
                        <template #body="slotProps">
                            {{ formatCurrency(slotProps.data.total_a_pagar) }}
                        </template>
                    </Column>
                    <Column field="nombre_cobrador" header="Cobrador" />
                    <Column field="saldo_restante" header="Saldo">
                        <template #body="slotProps">
                            {{ formatCurrency(slotProps.data.saldo_restante) }}
                        </template>
                    </Column>
                    <Column field="fecha_cancelado" header="Fecha Cancelado">
                        <template #body="slotProps">
                            {{ slotProps.data.fecha_cancelado ? new
                                Date(slotProps.data.fecha_cancelado).toLocaleDateString('es-ES') : "Sin fecha" }}
                        </template>
                    </Column>
                    <Column header="Estado">
                        <template #body="slotProps">
                            <i class="pi pi-circle-on"
                                :style="{ color: getColorForEstado(slotProps.data.estado, slotProps.data.proximo_pago) }"></i>
                            {{ slotProps.data.estado === "Completado" ? 'Completado' : (new
                                Date(slotProps.data.proximo_pago) < new Date() ? 'Atrasado' : slotProps.data.estado) }}
                                </template>
                    </Column>

                    <Column header="Acciones">
                        <template #body="slotProps">
                            <Button v-if="slotProps.data.estado != 'Pagado'" icon="pi pi-inbox"
                                class="p-button-success p-button-sm"
                                @click="abrirModal(slotProps.data, slotProps.index)" />
                            <Button v-else icon="pi pi-check" class="p-button-success p-button-sm" />
                            <Button icon="pi pi-file-pdf" class="p-button-danger p-button-sm"
                                @click="generarRecibo(slotProps.data.id)" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </Dialog>


        <AbonarCuota v-if="cuotaSeleccionada" :cuota="cuotaSeleccionada" :modal="modal" :moneda="monedaVenta"
            :ventaCredito="arraySeleccionado" :arrayCuotas="arrayCuotas" @cerrar-modal="cerrarModal" />
    </div>
</template>

<script>

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
            criterioOptions: [
                { label: 'Nombre del Cliente', value: 'nombre_cliente' },
                { label: 'Nombre del Vendedor', value: 'nombre_vendedor' },
                // Añade más opciones según sea necesario
            ],
            filtroAvanzadoOptions: [
                { label: 'Todos', value: '' },
                { label: 'Completo', value: 'completo' },
                { label: 'Pendientes', value: 'pendientes' },
                { label: 'Atrasado', value: 'atrasado' },
            ],
            filtroAvanzado: "",
            monedaVenta: [],
            ultimaCuota: null,
            modalDetalle: false,
            arraySeleccionado: null,
            cuotaSeleccionada: null,
            arrayCuotas: [],
            arrayCreditos: [],
            errores: {},
            modal: 0,
            tituloModal: '',
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            criterio: 'nombre_cliente',
            buscar: ''
        }
    },
    computed: {
        isActived: function () {
            return this.pagination.current_page;
        },
        pagesNumber: function () {
            if (!this.pagination.to) {
                return [];
            }
            let from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        formatCurrency(value) {
            if (value == null || isNaN(value)) return '0.00 ' + this.monedaVenta[1];
            return (parseFloat(value) * this.monedaVenta[0]).toFixed(2) + ' ' + this.monedaVenta[1];
        },
        formatDate(date) {
            if (!date) return 'Sin fecha';
            return new Date(date).toLocaleDateString('es-ES');
        },
        getEstadoText(cuota) {
            if (cuota.estado === 'Pagado') return 'Pagado';
            if (cuota.estado === 'Parcial') return 'Pago Parcial';
            if (cuota.estado === 'Completado') return 'Completado'; // Nuevo caso para Completado
            if (new Date(cuota.fecha_pago) < new Date()) return 'Atrasado';
            return cuota.estado;
        },
        async generarReciboGeneral(id_credito) {
            try {
                const response = await axios.get(`/credito/recibo-general/${id_credito}`, {
                    responseType: 'blob' // Obtener el PDF como Blob
                });

                const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `recibo-general-${id_credito}.pdf`);
                link.click();
            } catch (error) {
                console.error('Error al generar el recibo:', error);
                this.toastError('No se pudo generar el recibo.');
            }
        },
        async generarRecibo(idCuota) {
            if (idCuota) {
                try {
                    const response = await axios.get(`/credito/recibo-cuota/${idCuota}`, {
                        responseType: 'blob' // Obtener el PDF como Blob
                    });

                    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `recibo-cuota-${idCuota}.pdf`);
                    link.click();
                } catch (error) {
                    console.error('Error al generar el recibo:', error);
                    this.toastError('No se pudo generar el recibo.');
                }
            } else {
                console.error('ID de cuota no válido');
                this.toastError('No se pudo generar el recibo. ID de cuota no válido.');
            }
        },
        calcularCuotasPagadas(totalVenta, montoRestante, numeroCuotas) {
            const montoPagado = totalVenta - montoRestante;
            const cuotasPagadas = Math.round(montoPagado / (totalVenta / numeroCuotas));
            return Math.min(cuotasPagadas, numeroCuotas); // Aseguramos que no exceda el número total de cuotas
        },
        getColorForEstado(estado, fecha_final) {
            const fechaFinal = new Date(fecha_final) < new Date();
            if (estado === "Completado") {
                return '#5ebf5f'; // Verde para Completado
            }
              if (estado === "Parcial") {
        return '#FF8C00'; 
    }
    if (estado === "Pendiente") {
        return '#007BFF'; 
    }

            if (fechaFinal && estado !== "Pagado") {
                return '#ff0000'; // Rojo para Atrasado
            }
            switch (estado) {
                case 'Pagado':
                    return '#5ebf5f'; // Verde para Pagado
                case 'Inactivo':
                    return '#d76868';
                case 'Atrasado':
                    return '#ce4444';
                default:
                    return '#f9dda6';
            }
        },
        cerrarModalDetalle() {
            this.modalDetalle = false;
            this.obtenerCreditos(1, this.buscar, this.criterio, this.filtroAvanzado);
        },
        abrirDetalle(credito) {
            this.arraySeleccionado = credito;
            this.obtenerCuotasCredito(credito.id);
            this.modalDetalle = true;
        },
        async obtenerCuotasCredito(idCredito) {
            try {
                const response = await axios.post('/credito/cuotas', { id_credito: idCredito });
                this.arrayCuotas = response.data;
            } catch (error) {
                console.error('Error al obtener las cuotas de crédito:', error);
            }
        },
        async obtenerCreditos(page, buscar, criterio, filtroAvanzado) {
            try {
                const response = await axios.get('/credito', {
                    params: {
                        page: page,
                        buscar: buscar,
                        criterio: criterio,
                        filtro_avanzado: filtroAvanzado
                    }
                });
                this.arrayCreditos = response.data.creditos.data;
                this.pagination = response.data.pagination;
            } catch (error) {
                console.error('Error al obtener los créditos de venta:', error);
            }
        },
        formatFecha(fechaOriginal) {
            const fecha = new Date(fechaOriginal);
            const dia = fecha.getDate();
            const mes = fecha.getMonth() + 1;
            const anio = fecha.getFullYear();

            const diaFormateado = dia < 10 ? `0${dia}` : dia;
            const mesFormateado = mes < 10 ? `0${mes}` : mes;

            return `${diaFormateado}-${mesFormateado}-${anio}`;
        },
        cambiarPagina(event) {
            const page = event.page + 1;  // PrimeVue usa paginación basada en 0, así que sumamos 1
            this.obtenerCreditos(page, this.buscar, this.criterio, this.filtroAvanzado);
        },
        cerrarModal() {
            this.modal = 0;
            this.obtenerCuotasCredito(this.arraySeleccionado.id);
        },
        abrirModal(cuota, index) {
            this.modal = 1;
            this.cuotaSeleccionada = {
                ...cuota,
                index,
                total_a_pagar: cuota.precio_cuota + cuota.recargo,
                saldo_restante: cuota.saldo_restante
            };
        },
        toastSuccess(mensaje) {
            this.$toasted.show(`<div style="height: 60px;font-size:16px;"><br>` + mensaje + `.<br></div>`, {
                type: "success",
                position: "bottom-right",
                duration: 4000
            });
        },
        toastError(mensaje) {
            this.$toasted.show(`<div style="height: 60px;font-size:16px;"><br>` + mensaje + `<br></div>`, {
                type: "error",
                position: "bottom-right",
                duration: 4000
            });
        },
        async datosConfiguracion() {
            try {
                const response = await axios.get('/configuracion');
                const respuesta = response.data;
                this.monedaVenta = [respuesta.configuracionTrabajo.valor_moneda_venta, respuesta.configuracionTrabajo.simbolo_moneda_venta];
            } catch (error) {
                console.log(error);
            }
        },
        exportarPDF() {
            window.open('/creditos/exportar-pdf', '_blank');
        },
        exportarExcel() {
            window.open('/creditos/exportar-excel', '_blank');
        },
    },
    mounted() {
        this.obtenerCreditos(1, this.buscar, this.criterio, this.filtroAvanzado);
        this.datosConfiguracion();
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
