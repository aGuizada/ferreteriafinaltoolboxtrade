<template>
    <main class="main">
        <Panel>
            <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
            </Toast>
            <template #header>
                <div class="panel-header">

                    <h4 class="panel-icon">Apertura/Cierre de Caja
                    </h4>
                </div>
            </template>
            <template>
                <div class="card-header">
                    <Button @click="abrirModal('caja', 'registrar')" icon="pi pi-plus" label="Abrir Caja"
                        class="p-button-secondary" />
                </div>
            </template>

            <DataTable :value="arrayCaja" :paginator="true" :rows="10" :totalRecords="pagination.total" :lazy="true"
                @page="onPage($event)" :first="pagination.from - 1" responsiveLayout="scroll"
                class="p-datatable-gridlines p-datatable-sm moto-table" :responsive="true">
                <Column field="id" header="N°"></Column>
                <Column field="estado" header="Estado">
                    <template #body="slotProps">
                        <Tag :severity="slotProps.data.estado ? 'success' : 'danger'">
                            {{ slotProps.data.estado ? 'ABIERTO' : 'CERRADO' }}
                        </Tag>
                    </template>
                </Column>

                <Column field="fechaApertura" header="Fecha Apertura"></Column>
                <Column field="fechaCierre" header="Fecha Cierre"></Column>
                <Column field="saldoInicial" header="Saldo Inicial"></Column>
                <Column field="ventas" header="Ventas Totales"></Column>


                <Column field="cuotasventasCredito" header="Pagos de Cuotas"></Column>
                <Column field="ventasContado" header="Ventas al Contado">
    <template #body="slotProps">
        {{ formatCurrency(slotProps.data.ventasContado || 0) }}
    </template>
</Column>
<Column field="pagosQR" header="Pagos QR">
    <template #body="slotProps">
        {{ formatCurrency(slotProps.data.pagosQR || 0) }}
    </template>
</Column>
<Column field="ventasAdelantadas" header="Ventas Adelantadas">
    <template #body="slotProps">
        {{ formatCurrency(slotProps.data.ventasAdelantadas || 0) }}
    </template>
</Column>
<Column field="comprasContado" header="Compras al Contado">
    <template #body="slotProps">
        {{ formatCurrency(slotProps.data.comprasContado || 0) }}
    </template>
</Column>
<Column field="comprasCredito" header="Compras a Crédito">
    <template #body="slotProps">
        {{ formatCurrency(slotProps.data.comprasCredito || 0) }}
    </template>
</Column>
<Column field="pagosTransferencia" header="Pagos por Transferencia">
    <template #body="slotProps">
        {{ formatCurrency(slotProps.data.pagosTransferencia || 0) }}
    </template>
</Column>
                <Column field="saldoFaltante" header="Saldo de faltante"></Column>
                <Column field="depositos" header="Depósitos Extras"></Column>
                <Column field="salidas" header="Salidas Extras"></Column>
                <Column field="saldoCaja" header="Saldo Caja"></Column>

                <Column header="Acciones">
                    <template #body="slotProps">
                        <Button icon="pi pi-plus" class="p-button-primary p-button-sm"
                            @click="abrirModal2('cajaDepositar', 'depositar', slotProps.data)"
                            v-if="slotProps.data.estado && !arqueoRealizado" />
                        <Button icon="pi pi-minus" class="p-button-danger p-button-sm"
                            @click="abrirModal3('cajaRetirar', 'retirar', slotProps.data)"
                            v-if="slotProps.data.estado && !arqueoRealizado" />
                        <Button icon="pi pi-eye" class="p-button-warning p-button-sm"
                            @click="abrirModal4('cajaVer', 'ver', slotProps.data.id)" />
                        <Button icon="pi pi-calculator" class="p-button-success p-button-sm"
                            @click="abrirModal5('arqueoCaja', 'contar', slotProps.data.id)"
                            v-if="slotProps.data.estado && !arqueoRealizado" />
                        <Button icon="pi pi-lock" class="p-button-danger p-button-sm"
                            @click="cerrarCaja(slotProps.data.id)" v-if="slotProps.data.estado && arqueoRealizado" />
                            <Button icon="pi pi-file-pdf" class="p-button-success p-button-sm" 
                            @click="generarPDF(slotProps.data.id)" v-if="!slotProps.data.estado" v-tooltip.top="'Descargar PDF'"/>
                    </template>
                </Column>
            </DataTable>

            <template>
                <Dialog :visible.sync="modal" :modal="true" :header="tituloModal" @hide="cerrarModal"
                    containerStyle="width: 500px;">
                    <form @submit.prevent="registrarCaja" class="p-fluid">
                        <div class="p-field p-grid">
                            <label for="saldoInicial" class="p-col-12 p-md-3">Saldo Inicial</label>
                            <div class="p-col-12 p-md-9">
                                <InputText id="saldoInicial" v-model="saldoInicial" placeholder="0.00"
                                    @keyup.enter="registrarCaja" />
                            </div>
                        </div>
                        <div v-if="errorCaja" class="p-field p-grid div-error">
                            <div class="p-col text-center text-error">
                                <div v-for="error in errorMostrarMsjCaja" :key="error" v-text="error"></div>
                            </div>
                        </div>
                    </form>
                    <template #footer>
                        <Button label="Cerrar" icon="pi pi-times" class="p-button-danger" @click="cerrarModal" />
                        <Button v-if="tipoAccion == 1" label="Guardar" icon="pi pi-check" class="p-button-success"
                            @click="registrarCaja" />
                    </template>
                </Dialog>
            </template>

            <template>
                <Dialog :visible.sync="modal2" :modal="true" :header="tituloModal2" @hide="cerrarModal2"
                    containerStyle="width: 500px">
                    <form @submit.prevent="depositar" enctype="multipart/form-data" class="p-fluid">
                        <div class="p-field p-grid">
                            <label for="depositos" class="p-col-12 p-md-3">Importe</label>
                            <div class="p-col-12 p-md-9">
                                <InputText id="depositos" v-model="depositos" placeholder="0.00" />
                            </div>
                        </div>
                        <div class="p-field p-grid">
                            <label for="Desdepositos" class="p-col-12 p-md-3">Descripcion de Importe</label>
                            <div class="p-col-12 p-md-9">
                                <InputText id="Desdepositos" v-model="Desdepositos" placeholder="Descripcion" />
                            </div>
                        </div>
                        <div v-if="errorCaja" class="p-field p-grid div-error">
                            <div class="p-col text-center text-error">
                                <div v-for="error in errorMostrarMsjCaja" :key="error" v-text="error"></div>
                            </div>
                        </div>
                    </form>
                    <template #footer>
                        <Button label="Cerrar" icon="pi pi-times" class="p-button-danger" @click="cerrarModal2" />
                        <Button v-if="tipoAccion == 2" label="Depositar" icon="pi pi-arrow-right"
                            class="p-button-success" @click="depositar" />
                    </template>
                </Dialog>
            </template>


            <template>
                <Dialog :visible.sync="modal3" :modal="true" :header="tituloModal3" @hide="cerrarModal3"
                    containerStyle="width: 500px">
                    <form @submit.prevent="retirar" enctype="multipart/form-data" class="p-fluid">
                        <div class="p-field p-grid">
                            <label for="salidas" class="p-col-12 p-md-3">Importe</label>
                            <div class="p-col-12 p-md-9">
                                <InputText id="salidas" v-model="salidas" placeholder="0.00" />
                            </div>
                        </div>
                        <div class="p-field p-grid">
                            <label for="Dessalidas" class="p-col-12 p-md-3">Descripcion de Importe</label>
                            <div class="p-col-12 p-md-9">
                                <InputText id="Dessalidas" v-model="Dessalidas" placeholder="Descripcion" />
                            </div>
                        </div>
                        <div v-if="errorCaja" class="p-field p-grid div-error">
                            <div class="p-col text-center text-error">
                                <div v-for="error in errorMostrarMsjCaja" :key="error" v-text="error"></div>
                            </div>
                        </div>
                    </form>
                    <template #footer>
                        <Button label="Cerrar" icon="pi pi-times" class="p-button-danger" @click="cerrarModal3" />
                        <Button v-if="tipoAccion == 3" label="Retirar" icon="pi pi-arrow-right" class="p-button-success"
                            @click="retirar" />
                    </template>
                </Dialog>
            </template>


            <template>
                <Dialog :visible.sync="modal4" :modal="true" :header="tituloModal4" @hide="cerrarModal4"
                    containerStyle="width: 800px; overflow-y: auto;">
                    <b-tabs content-class="mt-3">
                        <b-tab title="Compras Realizadas (egresos)" active>
                            <TransaccionErgeso v-if="egreso" :data="egreso" />
                        </b-tab>
                        <b-tab title="Ventas Realizadas (ingresos)">
                            <TransaccionIngreso v-if="ingreso" :data="ingreso" />
                        </b-tab>
                        <b-tab title="Transacciones Extras">
                            <TransaccionExtra v-if="extra" :data="extra" />
                        </b-tab>
                    </b-tabs>
                    <template #footer>
                        <Button label="Cerrar" icon="pi pi-times" class="p-button-danger" @click="cerrarModal4" />
                    </template>
                </Dialog>
            </template>

            <template>
                <Dialog :visible.sync="modal5" :modal="true" :header="tituloModal5" @hide="cerrarModal5"
                    containerStyle="width: 800px">
                    <form @submit.prevent="registrarArqueo" enctype="multipart/form-data" class="p-fluid">


                        <div class="p-grid">
                            <div class="p-col-12 p-md-6">
                                <h4>Billetes</h4>
                                <div class="p-field p-grid">
                                    <label for="billete200" class="p-col-12">
                                        <img src="img/caja/200.jpg" alt="Bs. 200" class="billete-img">
                                    </label>
                                    <div class="p-col-5 ">
                                        <InputNumber id="billete200" v-model="billete200" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="billete100" class="p-col-12">
                                        <img src="img/caja/100.jpg" alt="Bs. 100" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="billete100" v-model="billete100" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="billete50" class="p-col-12">
                                        <img src="img/caja/50.jpg" alt="Bs. 50" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="billete50" v-model="billete50" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="billete20" class="p-col-12">
                                        <img src="img/caja/20.jpg" alt="Bs. 20" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="billete20" v-model="billete20" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="billete10" class="p-col-12">
                                        <img src="img/caja/10.jpg" alt="Bs. 10" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="billete10" v-model="billete10" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label class="p-col-12 p-md-4">Total Billetes Bs.</label>
                                    <div class="p-col-12 p-md-6">
                                        <span v-text="totalBilletes" class="font-weight-bold"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-col-12 p-md-6">
                                <h4>Monedas</h4>
                                <div class="p-field p-grid">
                                    <label for="moneda5" class="p-col-12">
                                        <img src="img/caja/5.jpg" alt="Bs. 5" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="moneda5" v-model="moneda5" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="moneda2" class="p-col-12">
                                        <img src="img/caja/2.jpg" alt="Bs. 2" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="moneda2" v-model="moneda2" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="moneda1" class="p-col-12">
                                        <img src="img/caja/1.jpg" alt="Bs. 1" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="moneda1" v-model="moneda1" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="moneda050" class="p-col-12">
                                        <img src="img/caja/c50.jpg" alt="Bs. 0.50" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="moneda050" v-model="moneda050" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="moneda020" class="p-col-12">
                                        <img src="img/caja/20M.jpg" alt="Bs. 0.20" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="moneda020" v-model="moneda020" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label for="moneda010" class="p-col-12">
                                        <img src="img/caja/c10.jpg" alt="Bs. 0.10" class="billete-img">
                                    </label>
                                    <div class="p-col-5">
                                        <InputNumber id="moneda010" v-model="moneda010" :min="0" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus" />
                                    </div>
                                </div>
                                <div class="p-field p-grid">
                                    <label class="p-col-12 p-md-4">Total Monedas Bs.</label>
                                    <div class="p-col-12 p-md-8">
                                        <span v-text="totalMonedas" class="font-weight-bold"></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                    <template #footer>
                        <div class="p-field p-grid">
                            <label class="font-bold">Saldo en Sistema:</label>
                            <div class="p-col-2 ml-2">
                                <strong>Bs. {{ saldoSistema }}</strong>
                            </div>
                        </div>
                        <div class="p-field p-grid">
                            <label class="font-bold">Total Efectivo:</label>
                            <div class="p-col-2 ml-2">
                                <strong>Bs. {{ totalEfectivo }}</strong>
                            </div>
                        </div>
                        <Button label="Cancelar" icon="pi pi-times" class="p-button-danger" @click="cerrarModal5" />
                        <Button v-if="tipoAccion == 5" label="Guardar" icon="pi pi-check" class="p-button-success"
                            @click.prevent="registrarArqueo" />
                    </template>
                </Dialog>

            </template>
            <template>
                <!-- ... resto de tu template ... -->

                <div v-if="modalResumen" class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Resumen de Caja</h5>
                                <button type="button" class="close" @click="modalResumen = false">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" v-if="resumenCaja">
                                <h5>Movimientos:</h5>
                                <ul class="list-group mb-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center"
                                        v-for="movimiento in resumenCaja.movimientos" :key="movimiento.concepto">
                                        {{ movimiento.concepto }}
                                        <span class="badge badge-primary badge-pill">${{ movimiento.monto }}</span>
                                    </li>
                                </ul>
                                <h5>Resumen:</h5>
                                <div class="card">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total Ingresos
                                            <span class="text-success">${{ resumenCaja.totalIngresos.toFixed(2)
                                                }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total Egresos
                                            <span class="text-danger">${{ resumenCaja.totalEgresos.toFixed(2) }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Saldo en Caja
                                            <span class="font-weight-bold">${{ resumenCaja.saldoCaja }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    @click="modalResumen = false">Cancelar</button>
                                <button type="button" class="btn btn-primary" @click="realizarCierreCaja">Cerrar
                                    Caja</button>
                                    <button @click="generarPDF(resumenCaja.id)" class="btn btn-success">
                                        <i class="pi pi-file-pdf"></i> Generar PDF
                                    </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="modalResumen" class="modal-backdrop fade show"></div>
            </template>
        </Panel>
    </main>
</template>

<script>
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Panel from 'primevue/panel';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import TransaccionEgreso from "./Tables/TransaccionEgreso.vue";
import TransaccionExtra from "./Tables/TransaccionExtra.vue";
import TransaccionIngreso from "./Tables/TransaccionIngreso.vue";
export default {
    components: {
        DataTable,
        Column,
        Button,
        Dialog,
        InputText,
        Dropdown,
        Tag,
        InputNumber,
        TabView,
        TabPanel,
        Card,
        Toast,
        Panel,
        TransaccionEgreso,
        TransaccionIngreso,
        TransaccionExtra
    },
    data() {
        return {
            pagosQR: 0,
            saldoSistema: 0,
            estaRegistrando: false,
            resumenCaja: null,
            modalResumen: false,
            resumenCaja: {
                ventas: [],
                totalIngresos: 0,
                totalEgresos: 0
            },
            id: 0,
            idsucursal: 0,
            nombre_sucursal: '',
            idusuario: 0,
            usuario: '',
            fechaApertura: '',
            fechaCierre: '',
            saldoInicial: '',
            depositos: '',
            Desdepositos: '',
            salidas: '',
            Dessalidas: '',
            ventasContado: '',
            ventasCredito: '',
            comprasContado: '',
            comprasCredito: '',
            saldoFaltante: '',
            PagoCuotaEfectivo: '',
            saldoCaja: '',
            arqueo_id: 0,
            billete200: 0,
            billete100: 0,
            billete50: 0,
            billete20: 0,
            billete10: 0,
            totalBilletes: 0,
            moneda5: 0,
            moneda2: 0,
            moneda1: 0,
            moneda050: 0,
            moneda020: 0,
            moneda010: 0,
            totalMonedas: 0,
            arrayCaja: [],
            arrayTransacciones: [],
            ArrayIngresos: [],
            ArrayEgresos: [],
            egreso: null,
            ingreso: null,
            extra: null,
            modal: false,
            modal2: false,
            modal3: false,
            modal4: false,
            modal5: false,
            tituloModal: '',
            tituloModal2: '',
            tituloModal3: '',
            tituloModal4: '',
            tituloModal5: '',
            tipoAccion: 0,
            arqueoRealizado: false,
            errorCaja: 0,
            errorMostrarMsjCaja: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            arraySucursal: [],
            arrayUser: [],
            buscar: '',
            criterio: '',
            mostrarBotonesSecundarios: false
        }
    },
    computed: {
        totalEfectivo() {
            return parseFloat((this.totalBilletes + this.totalMonedas).toFixed(2));
        },
        totalBilletes() {
            return parseFloat((
                (Number(this.billete200) || 0) * 200 +
                (Number(this.billete100) || 0) * 100 +
                (Number(this.billete50) || 0) * 50 +
                (Number(this.billete20) || 0) * 20 +
                (Number(this.billete10) || 0) * 10
            ).toFixed(2));
        },
        totalMonedas() {
            return parseFloat((
                (Number(this.moneda5) || 0) * 5 +
                (Number(this.moneda2) || 0) * 2 +
                (Number(this.moneda1) || 0) * 1 +
                (Number(this.moneda050) || 0) * 0.5 +
                (Number(this.moneda020) || 0) * 0.2 +
                (Number(this.moneda010) || 0) * 0.1
            ).toFixed(2));
        },
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

        totalEfectivo() {
            return this.totalBilletes + this.totalMonedas;
        }
    },
    methods: {

        
        generarPDF(id) {
    axios({
        url: `/caja/resumen-pdf/${id}`,  // Cambiado a la ruta correcta
        method: 'GET',
        responseType: 'blob',
    }).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `resumen_caja_${id}.pdf`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }).catch(error => {
        this.$toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Error al generar el PDF: ' + error.message,
            life: 3000
        });
    });
},
descargarPDF(cajaId) {
    if (!cajaId) {
        this.$toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se encontró el ID de la caja para generar el PDF',
            life: 3000
        });
        return;
    }

    axios({
        url: `/caja/generar-pdf/${cajaId}`,
        method: 'GET',
        responseType: 'blob',
    }).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `resumen_caja_${cajaId}.pdf`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }).catch(error => {
        this.$toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Error al generar el PDF: ' + error.message,
            life: 3000
        });
    });
},

    abrirModalResumen() {
        this.modalResumen = true;
    },
    cerrarDialog() {
        this.modalResumen = false;
    },
    formatCurrency(value) {
        return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(value);
    },
    onPage(event) {
        this.listarCaja(event.page + 1, this.buscar, this.criterio);
    },
    mostrarError(mensaje) {
        this.$toast.add({ severity: 'error', summary: 'Error', detail: mensaje, life: 3000 });
    },
    listarCaja(page, buscar, criterio) {
        let me = this;
        var url = '/caja?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
        axios.get(url).then(function (response) {
            var respuesta = response.data;
            me.arrayCaja = respuesta.cajas.data;
            me.pagination = {
                total: respuesta.cajas.total,
                current_page: respuesta.cajas.current_page,
                per_page: respuesta.cajas.per_page,
                last_page: respuesta.cajas.last_page,
                from: respuesta.cajas.from,
                to: respuesta.cajas.to
            };
        })
            .catch(function (error) {
                console.log(error);
            });
    },

    cambiarPagina(page, buscar, criterio) {
        let me = this;
        me.pagination.current_page = page;
        me.listarCaja(page, buscar, criterio);
    },
    registrarCaja() {
        if (this.estaRegistrando) return;
        this.estaRegistrando = true;

        if (this.validarCaja()) {
            this.estaRegistrando = false;
            return;
        }

        let me = this;
        let formData = new FormData();
        formData.append('saldoInicial', this.saldoInicial);

        axios.post('/caja/registrar', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }).then(function (response) {
            me.cerrarModal();
            me.arqueoRealizado = false;
            me.listarCaja(1, '', 'id');
            swal(
                'Aperturada!',
                'Caja aperturada de forma satisfactoria!',
                'success'
            )
        }).catch(function (error) {
            console.log(error);
        }).finally(function () {
            me.estaRegistrando = false;
        });
    },
    async registrarArqueo() {
        let me = this;
        const totalArqueo = parseFloat(this.totalEfectivo.toFixed(2));

        try {
            const saldoCaja = parseFloat((await this.obtenerSaldoCaja()).toFixed(2));

            if (totalArqueo === 0) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'Debe ingresar al menos un valor para el arqueo',
                    life: 3000
                });
                return;
            }

            const diferencia = Math.abs(totalArqueo - saldoCaja);
            if (diferencia > 0.01) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: `El total del arqueo (${totalArqueo.toFixed(2)}) no coincide con el saldo de caja (${saldoCaja.toFixed(2)})`,
                    life: 3000
                });
                return;
            }

            const response = await axios.post('/caja/arqueoCaja', {
                'idcaja': this.id,
                'billete200': this.billete200,
                'billete100': this.billete100,
                'billete50': this.billete50,
                'billete20': this.billete20,
                'billete10': this.billete10,
                'moneda5': this.moneda5,
                'moneda2': this.moneda2,
                'moneda1': this.moneda1,
                'moneda050': this.moneda050,
                'moneda020': this.moneda020,
                'moneda010': this.moneda010,
                'totalArqueo': totalArqueo
            });

            me.cerrarModal5();
            me.arqueoRealizado = true;
            this.limpiarDatosArqueo();
            me.$toast.add({
                severity: 'success',
                summary: 'Éxito',
                detail: 'Arqueo de caja registrado correctamente',
                life: 3000
            });
            this.listarCaja(1, this.buscar, this.criterio);
        } catch (error) {
            console.error('Error al registrar el arqueo:', error);
            me.$toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo registrar el arqueo de caja',
                life: 3000
            });
        }
    },

    limpiarDatosArqueo() {
        this.billete200 = 0;
        this.billete100 = 0;
        this.billete50 = 0;
        this.billete20 = 0;
        this.billete10 = 0;
        this.moneda5 = 0;
        this.moneda2 = 0;
        this.moneda1 = 0;
        this.moneda050 = 0;
        this.moneda020 = 0;
        this.moneda010 = 0;
        this.totalBilletes = 0;
        this.totalMonedas = 0;
    },

    obtenerSaldoCaja() {
        return new Promise((resolve, reject) => {
            axios.get(`/caja/saldo/${this.id}`)
                .then(response => resolve(parseFloat(response.data.saldo)))
                .catch(error => {
                    console.error('Error al obtener el saldo de caja:', error);
                    reject(error);
                });
        });
    },

    depositar() {
        let me = this;

        if (!this.depositos || parseFloat(this.depositos) <= 0) {
            swal(
                'Error!',
                'El importe del depósito debe ser mayor a 0.',
                'error'
            );
            return;
        }

        axios.put('/caja/depositar', {
            'depositos': parseFloat(this.depositos),
            'id': this.id,
            'transaccion': `${this.Desdepositos} (movimiento de ingreso)`,
        }).then(function (response) {
            me.cerrarModal2();
            me.listarCaja(1, '', 'id');
            swal(
                'Información!',
                'Depósito realizado con éxito.',
                'success'
            );
        }).catch(function (error) {
            console.error('Error en el depósito:', error);
            swal(
                'Error!',
                'No se pudo realizar el depósito.',
                'error'
            );
        });
    },

    retirar() {
        let me = this;

        if (!this.salidas || parseFloat(this.salidas) <= 0) {
            swal(
                'Error!',
                'El importe del retiro debe ser mayor a 0.',
                'error'
            );
            return;
        }

        axios.put('/caja/retirar', {
            'salidas': parseFloat(this.salidas),
            'id': this.id,
            'transaccion': `${this.Dessalidas} (movimiento de egreso)`,
        }).then(function (response) {
            me.cerrarModal3();
            me.listarCaja(1, '', 'id');
            swal(
                'Información!',
                'Retiro realizado con éxito.',
                'success'
            );
        }).catch(function (error) {
            console.error('Error en el retiro:', error);
            swal(
                'Error!',
                'No se pudo realizar el retiro.',
                'error'
            );
        });
    },

    calcularTotalBilletes() {
        const billete200 = parseFloat(this.billete200) || 0;
        const billete100 = parseFloat(this.billete100) || 0;
        const billete50 = parseFloat(this.billete50) || 0;
        const billete20 = parseFloat(this.billete20) || 0;
        const billete10 = parseFloat(this.billete10) || 0;

        this.totalBilletes = billete200 * 200 + billete100 * 100 + billete50 * 50 + billete20 * 20 + billete10 * 10;
    },

    calcularTotalMonedas() {
        const moneda5 = parseFloat(this.moneda5) || 0;
        const moneda2 = parseFloat(this.moneda2) || 0;
        const moneda1 = parseFloat(this.moneda1) || 0;
        const moneda050 = parseFloat(this.moneda050) || 0;
        const moneda020 = parseFloat(this.moneda020) || 0;
        const moneda010 = parseFloat(this.moneda010) || 0;

        this.totalMonedas = moneda5 * 5 + moneda2 * 2 + moneda1 * 1 + moneda050 * 0.50 + moneda020 * 0.20 + moneda010 * 0.10;
    },

    async cerrarCaja(id) {
        this.modalResumen = true;
        try {
            const response = await axios.get(`/caja/resumen/${id}`);
            this.resumenCaja = response.data;
        } catch (error) {
            console.error('Error al obtener el resumen de caja:', error);
            this.$toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo obtener el resumen de caja', life: 3000 });
        }
    },

    async realizarCierreCaja() {
        try {
            if (!this.arqueoRealizado) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'Debe realizar el arqueo de caja antes de cerrarla',
                    life: 3000
                });
                return;
            }

            const saldoArqueo = this.totalEfectivo;
            const resumenCaja = await axios.get(`/caja/resumen/${this.resumenCaja.id}`);
            const {
                saldoInicial,
                pagosQR,
                ventasContado,
                ventasAdelantadas,
                totalIngresos,
                saldoCaja
            } = resumenCaja.data;

            const saldoFaltante = saldoCaja - saldoArqueo;

            await axios.put('/caja/cerrar', {
                'id': this.resumenCaja.id,
                'saldoFaltante': saldoFaltante
            });

            this.$toast.add({
                severity: 'success',
                summary: 'Cierre de Caja',
                detail: `
Saldo Inicial: Bs. ${saldoInicial.toFixed(2)}
Ventas al Contado: Bs. ${ventasContado.toFixed(2)}
Pagos QR: Bs. ${pagosQR.toFixed(2)}
Ventas Adelantadas: Bs. ${ventasAdelantadas.toFixed(2)}
Saldo Total (Sistema): Bs. ${saldoCaja.toFixed(2)}
Total Efectivo (Arqueo): Bs. ${saldoArqueo.toFixed(2)}
Diferencia (Faltante/Sobrante): Bs. ${saldoFaltante.toFixed(2)}
                `,
                life: 5000
            });

            await this.listarCaja(1, '', 'id');
            this.arqueoRealizado = false;
            this.modalResumen = false;
        } catch (error) {
            console.error('Error al realizar el cierre de caja:', error);
            this.$toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo realizar el cierre de caja',
                life: 3000
            });
        }
    } ,


    cajaAbierta() {
        for (let i = 0; i < this.arrayCaja.length; i++) {
            if (this.arrayCaja[i].estado) {
                return true;
            }
        }
        return false;
    },

    validarCaja() {
        this.errorCaja = 0;
        this.errorMostrarMsjCaja = [];

        if (!this.saldoInicial) this.errorMostrarMsjCaja.push("El saldo inicial no puede estar vacío.");

        if (this.errorMostrarMsjCaja.length) this.errorCaja = 1;

        return this.errorCaja;
    },
    cerrarModal() {
        this.modal = false;
        this.tituloModal = '';
        this.idsucursal = 0;
        this.sucursal = '';
        this.saldoInicial = '';
    },

    cerrarModal2() {
        this.modal2 = false;
        this.depositos = '';
        this.Desdepositos = '';
    },

    cerrarModal3() {
        this.modal3 = false;
        this.salidas = '';
        this.Dessalidas = '';
    },

    cerrarModal4() {
        this.modal4 = false;
    },

    cerrarModal5() {
        this.modal5 = false;
        this.limpiarDatosArqueo();
    },

    abrirModal(modelo, accion, data = []) {
        switch (modelo) {
            case "caja":
                {
                    switch (accion) {
                        case 'registrar':
                            {
                                if (this.cajaAbierta()) {
                                    swal(
                                        'Ya existe una caja abierta!',
                                        'Por favor realice el cierre de la caja e intente de nuevo.',
                                        'error'
                                    )
                                    return;
                                }

                                this.modal = true;
                                this.tituloModal = 'Apertura de Caja Sucursal: ';
                                this.saldoInicial = '';

                                this.tipoAccion = 1;
                                break;
                            }
                    }
                }
        }
    },

    abrirModal2(modelo, accion, data = []) {
        if (this.arqueoRealizado) {
            this.$toast.add({
                severity: 'warn',
                summary: 'Advertencia',
                detail: 'No se pueden realizar depósitos después del arqueo de caja',
                life: 3000
            });
            return;
        }
        switch (modelo) {
            case "cajaDepositar": {
                switch (accion) {
                    case 'depositar': {
                        this.modal2 = true;
                        this.tituloModal2 = 'Depositar Dinero';
                        this.id = data['id'];
                        this.tipoAccion = 2;
                        break;
                    }
                }
            }
        }
    },

    abrirModal3(modelo, accion, data = []) {
        if (this.arqueoRealizado) {
            this.$toast.add({
                severity: 'warn',
                summary: 'Advertencia',
                detail: 'No se pueden realizar retiros después del arqueo de caja',
                life: 3000
            });
            return;
        }
        switch (modelo) {
            case "cajaRetirar": {
                switch (accion) {
                    case 'retirar': {
                        this.modal3 = true;
                        this.tituloModal3 = 'Retirar Dinero';
                        this.id = data['id'];
                        this.tipoAccion = 3;
                        break;
                    }
                }
            }
        }
    },
    abrirModal4(modelo, accion, id) {
        switch (modelo) {
            case "cajaVer":
                {
                    switch (accion) {
                        case 'ver':
                            {
                                this.modal4 = true;
                                this.tituloModal4 = 'Transacciones Caja';

                                let me = this;
                                var url = '/transacciones/' + id;
                                axios.get(url).then(function (response) {
                                    var respuesta = response.data;

                                    me.arrayTransacciones = respuesta.transacciones.data;
                                    me.ArrayEgresos = respuesta.ingresos;
                                    me.ArrayIngresos = respuesta.ventas.data;

                                    me.egreso = respuesta.ingresos;
                                    me.ingreso = respuesta.ventas;
                                    me.extra = respuesta.transacciones;
                                })
                                    .catch(function (error) {
                                        console.log(error);
                                    });

                                break;
                            }
                    }
                }
        }
    },

    async abrirModal5(modelo, accion, id) {
        switch (modelo) {
            case "arqueoCaja": {
                switch (accion) {
                    case 'contar': {
                        this.limpiarDatosArqueo();
                        this.modal5 = true;
                        this.tituloModal5 = 'Arqueo de Caja';
                        this.id = id;
                        this.tipoAccion = 5;

                        try {
                            const response = await axios.get(`/caja/saldo/${id}`);
                            this.saldoSistema = parseFloat(response.data.saldo).toFixed(2);
                        } catch (error) {
                            console.error('Error al obtener el saldo:', error);
                            this.$toast.add({
                                severity: 'error',
                                summary: 'Error',
                                detail: 'No se pudo obtener el saldo de caja',
                                life: 3000
                            });
                        }
                        break;
                    }
                }
            }
        }
    }
},

    watch: {
        'billete200': 'calcularTotalBilletes',
        'billete100': 'calcularTotalBilletes',
        'billete50': 'calcularTotalBilletes',
        'billete20': 'calcularTotalBilletes',
        'billete10': 'calcularTotalBilletes',
        'moneda5': 'calcularTotalMonedas',
        'moneda2': 'calcularTotalMonedas',
        'moneda1': 'calcularTotalMonedas',
        'moneda050': 'calcularTotalMonedas',
        'moneda020': 'calcularTotalMonedas',
        'moneda010': 'calcularTotalMonedas'
    },

    mounted() {
        this.listarCaja(1, this.buscar, this.criterio);


    }
}
</script>
<style scoped>
.p-panel-header {
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

.billete-img {
    max-width: 230px;
    /* Ajusta el tamaño máximo según tus necesidades */
    height: auto;
    display: block;
    margin: 0 auto;
    /* Centra la imagen en su contenedor */
}

.p-field p-grid {
    margin-bottom: 1rem;
    /* Añade un margen inferior entre los campos */
}

.p-col-5 {
    margin-left: 115px;
}

.modal-content {
    border-radius: 6px;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-title {
    font-weight: 700;
    color: #495057;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

.badge-primary {
    background-color: #007bff;
}

.list-group-item {
    border: 1px solid #e9ecef;
}

.card {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
}
</style>
