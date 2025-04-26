<template>
    <main class="main">
        <Panel>
            <template #header>
                <div class="panel-header">
                    <i class="pi pi-bars"></i>
                    <h4 class="panel-title">Artículos</h4>
                </div>
            </template>
            <div class="toolbar-container">
                <div class="toolbar">
                    <Button label="Nuevo" icon="pi pi-plus" class="p-button-secondary p-button-sm" @click="abrirModal('articulo', 'registrar')"/>
                    <Button label="Reporte" icon="pi pi-file" class="p-button-success p-button-sm" @click="cargarPdf()" />
                    <Button label="Importar" icon="pi pi-upload" class="p-button-help p-button-sm" @click="abrirDialogos('Importar')" />
                </div>
                <div class="search-bar">
                    <span class="p-input-icon-left">
                        <i class="pi pi-search" />
                        <InputText v-model="buscar" placeholder="Texto a buscar" class="p-inputtext-sm" @keyup="buscarArticulo" />
                    </span>
                </div>
            </div>
            <DataTable :value="arrayArticulo" class="p-datatable-gridlines p-datatable-sm" responsiveLayout="scroll">
        <Column v-for="(column, index) in computedColumns" :key="index" :field="column.field" :header="column.header">
            <template #body="slotProps">
                <span v-if="column.type === 'button'">
                    <Button
                        v-if="column.field === 'acciones'"
                        icon="pi pi-pencil"
                        class="p-button-warning p-button-sm"
                        @click="abrirModal('articulo', 'actualizar', slotProps.data)"
                    />
                    <Button
                        v-if="column.field === 'acciones' && slotProps.data.condicion"
                        icon="pi pi-ban"
                        class="p-button-sm p-button-danger custom-icon-size"
                        @click="desactivarArticulo(slotProps.data.id)"
                    />
                    <Button
                        v-if="column.field === 'acciones' && !slotProps.data.condicion"
                        icon="pi pi-check-circle"
                        class="p-button-sm p-button-success custom-icon-size"
                        @click="activarArticulo(slotProps.data.id)"
                    />
                </span>
                <span v-else-if="column.type === 'dynamicPrice'">
                    {{ (slotProps.data[column.field] * parseFloat(monedaPrincipal[0])).toFixed(2) }} {{ monedaPrincipal[1] }}
                </span>
                <span v-else-if="column.type === 'image'">
                    <img :src="'img/articulo/' + slotProps.data.fotografia + '?t=' + new Date().getTime()" width="50" height="50" v-if="slotProps.data.fotografia" ref="imagen">
                    <img :src="'img/articulo/' + 'defecto.jpg'" width="50" height="50" v-else ref="imagen">
                </span>
                <span v-else-if="column.type === 'badge'" style="text-align: center;">
                    <span v-if="slotProps.data.condicion" class="badge badge-success" style="center">Si</span>
                    <span v-else class="badge badge-danger" style="center">No</span>
                </span>
                <span v-else>
                    {{ slotProps.data[column.field] }}
                </span>
            </template>
        </Column>
    </DataTable>
            <Paginator :rows="pagination.per_page" 
                   :totalRecords="pagination.total" 
                   :first="(pagination.current_page - 1) * pagination.per_page" 
                   @page="onPageChange" />
        </Panel>
        <!-- MODAL REGISTRAR PRODUCTO -->
        <template>
    <Dialog :visible.sync="dialogVisible" :modal="true" header="Registrar Artículo" :closable="true" @hide="closeDialog" :containerStyle="{width: '800px'}">
        <form>
            <!-- Primera fila: Nombre y Foto -->
            <div class="form-group row mb-4">
                <div class="col-md-6">
                    <div>
                        <label class="font-weight-bold" for="nombreProducto">Nombre del Producto <span class="text-danger">*</span></label>
                        <InputText id="nombreProducto" v-model="datosFormulario.nombre" placeholder="Ej nombre producto" class="form-control p-inputtext-sm" :class="{'p-invalid' : errores.nombre}" @input="validarCampo('nombre')"/>
                        <small class="p-error" v-if="errores.nombre"><strong>{{ errores.nombre }}</strong></small>
                    </div>
                </div>
             
                <div class="col-md-6">
                    <div>
                        <label class="font-weight-bold" for="foto">Foto del Producto</label>
                        <div class="container">
                            <div class="row">
                                <div class="d-flex justify-content-center">
                                    <div v-if="!imagen" class="bg-light p-5 rounded">
                                        <i class="fa fa-camera fa-2x" style="color:#6e6e6e" aria-hidden="true"></i>
                                    </div>
                                    <figure v-else>
                                        <ImagePreview :src="imagen" alt="Image" width="140" height="140" />
                                    </figure>
                                </div>
                            </div>
                            <div class="input-group mt-2">
                                <input type="file" @change="obtenerFotografia" class="form-control" placeholder="fotografia" ref="fotografiaInput">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="descripcion">Descripción <span class="text-danger">*</span></label>
                    <InputText id="descripcion" v-model="datosFormulario.descripcion" placeholder="Ingrese una descripción" class="form-control p-inputtext-sm" :class="{'p-invalid' : errores.descripcion}" @input="validarCampo('descripcion')"/>
                    <small class="p-error" v-if="errores.descripcion"><strong>{{ errores.descripcion }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="codigo">Código del Producto <span class="text-danger">*</span></label>
                    <InputText id="codigo" v-model="datosFormulario.codigo" placeholder="Ej. SKU123" class="form-control p-inputtext-sm" :class="{'p-invalid' : errores.codigo}" @input="validarCampo('codigo')"/>
                    <small class="p-error" v-if="errores.codigo"><strong>{{ errores.codigo }}</strong></small>
                    <div class="d-flex mt-4 justify-content-center" style="width:250px;overflow-x: auto;">
                        <barcode :value="datosFormulario.codigo" :options="{ format: 'EAN-13' }"></barcode>
                    </div>
                </div>
            </div>

            <!-- Segunda fila: Código de Producto y Código de Barras -->
           

            <!-- Tercera fila: Proveedor -->
            <div class="form-group row mb-4">
                <div class="col-md-6">
                    <label class="font-weight-bold" for="proveedor">Proveedor <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputText id="proveedor" v-model="proveedorSeleccionado.nombre" placeholder="Seleccione un proveedor" class="form-control p-inputtext-sm bold-input" disabled :class="{'p-invalid' : errores.idproveedor}"/>
                        <Button label="..." class="p-button-primary p-button-sm" @click="abrirDialogos('Proveedores')" />
                    </div>
                    <small class="p-error" v-if="errores.idproveedor"><strong>{{ errores.idproveedor }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="medida">Medidas <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputText id="medida" v-model="medidaSeleccionado.descripcion_medida" placeholder="Seleccione una medida" class="form-control p-inputtext-sm bold-input" disabled :class="{'p-invalid' : errores.idmedida}"/>
                        <Button label="..." class="p-button-primary p-button-sm" @click="abrirDialogos('Medidas')" />
                    </div>
                    <small class="p-error" v-if="errores.idmedida"><strong>{{ errores.idmedida }}</strong></small>
                </div>
            </div>

            <!-- Cuarta fila: Categoría y Marca -->
            <div class="form-group row mb-4">
                <div class="col-md-6">
                    <label class="font-weight-bold" for="Categoria">Categoria <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputText id="linea" v-model="lineaSeleccionado.nombre" placeholder="Seleccione una línea" class="form-control p-inputtext-sm bold-input" disabled :class="{'p-invalid' : errores.idcategoria}"/>
                        <Button label="..." class="p-button-primary p-button-sm" @click="abrirDialogos('Lineas')" />
                    </div>
                    <small class="p-error" v-if="errores.idcategoria"><strong>{{ errores.idcategoria }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="marca">Marca <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputText id="marca" v-model="marcaSeleccionado.nombre" placeholder="Seleccione una marca" class="form-control p-inputtext-sm bold-input" disabled :class="{'p-invalid' : errores.idmarca}"/>
                        <Button label="..." class="p-button-primary p-button-sm" @click="abrirDialogos('Marcas')" />
                    </div>
                    <small class="p-error" v-if="errores.idmarca"><strong>{{ errores.idmarca }}</strong></small>
                </div>
            </div>

            <!-- Quinta fila: Industria y Grupo/Familia -->
            <div class="form-group row mb-4">
                <div class="col-md-6">
                    <label class="font-weight-bold" for="industria">Industria <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputText id="industria" v-model="industriaSeleccionado.nombre" placeholder="Seleccione una industria" class="form-control p-inputtext-sm bold-input" disabled :class="{'p-invalid' : errores.idindustria}"/>
                        <Button label="..." class="p-button-primary p-button-sm" @click="abrirDialogos('Industrias')" />
                    </div>
                    <small class="p-error" v-if="errores.idindustria"><strong>{{ errores.idindustria }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="grupoFamilia">Grupo/Familia <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputText id="grupoFamilia" v-model="grupoSeleccionado.nombre_grupo" placeholder="Seleccione un grupo" class="form-control p-inputtext-sm bold-input" disabled :class="{'p-invalid' : errores.idgrupo}"/>
                        <Button label="..." class="p-button-primary p-button-sm" @click="abrirDialogos('Grupos')" />
                    </div>
                    <small class="p-error" v-if="errores.idgrupo"><strong>{{ errores.idgrupo }}</strong></small>
                </div>
            </div>

            <!-- Sexta fila: Stock y Medidas -->
            <div class="form-group row mb-4">
                <div class="col-md-6">
                    <label class="font-weight-bold" for="stockMinimo">Stock Mínimo <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputNumber id="stockMinimo" v-model="datosFormulario.stock" placeholder="Ej: 10" class="p-inputtext-sm" :class="{'p-invalid' : errores.stock}" @input="validarCampo('stock')"/>
                        <Dropdown v-model="tipo_stock" :options="tipoEnvase" optionLabel="etiqueta" optionValue="valor" class="p-dropdown-sm p-inputtext-sm" />
                    </div>
                    <small class="p-error" v-if="errores.stock"><strong>{{ errores.stock }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="unidadEnvase">Unidades por Paquete <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputNumber id="unidadEnvase" v-model="datosFormulario.unidad_envase" placeholder="Ej: 24" class="p-inputtext-sm" :class="{'p-invalid' : errores.unidad_envase}" @input="validarCampo('unidad_envase')"/>
                    </div>
                    <small class="p-error" v-if="errores.unidad_envase"><strong>{{ errores.unidad_envase }}</strong></small>
                </div>
                
            </div>

            <!-- Séptima fila: Precios -->
            <div class="form-group row mb-4">
                <div class="col-md-6">
                    <label class="font-weight-bold" for="preciounitario">Precio Unitario <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputNumber id="preciounitario" v-model="datosFormulario.precio_costo_unid" placeholder="Sin decimales" class="p-inputtext-sm bold-input" mode="decimal" :minFractionDigits="2" :class="{'p-invalid' : errores.precio_costo_unid}" @input="validarCampo('precio_costo_unid')"/>
                        <Button label="Calcular" class="p-button-primary p-button-sm" @click="calcularPrecioCostoUnid" />
                    </div>
                    <small class="p-error" v-if="errores.precio_costo_unid"><strong>{{ errores.precio_costo_unid }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="preciopaquete">Precio Paquete <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputNumber id="preciopaquete" v-model="datosFormulario.precio_costo_paq" placeholder="Sin decimales" class="p-inputtext-sm bold-input" mode="decimal" :minFractionDigits="2" :class="{'p-invalid' : errores.precio_costo_paq}" @input="validarCampo('precio_costo_paq')"/>
                        <Button label="Calcular" class="p-button-primary p-button-sm" @click="calcularPrecioCostoPaq" />
                    </div>
                    <small class="p-error" v-if="errores.precio_costo_paq"><strong>{{ errores.precio_costo_paq }}</strong></small>
                </div>
            </div>

            <!-- Octava fila: Costos -->
            <div class="form-group row mb-4">
                <div class="col-md-6">
                    <label class="font-weight-bold" for="costocompra">Costo compra <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputNumber id="costocompra" v-model="datosFormulario.costo_compra" placeholder="Sin decimales" class="p-inputtext-sm bold-input" mode="decimal" :minFractionDigits="2" :class="{'p-invalid' : errores.costo_compra}" @input="validarCampo('costo_compra')"/>
                    </div>
                    <small class="p-error" v-if="errores.costo_compra"><strong>{{ errores.costo_compra }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="font-weight-bold" for="precioventa">Precio Venta <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputNumber id="precioventa" v-model="datosFormulario.precio_venta" placeholder="Sin decimales" class="p-inputtext-sm bold-input" mode="decimal" :minFractionDigits="2" :class="{'p-invalid' : errores.precio_venta}" @input="validarCampo('precio_venta')"/>
                    </div><small class="p-error" v-if="errores.precio_venta"><strong>{{ errores.precio_venta }}</strong></small>
                </div>
            </div>

            <!-- Novena fila: Switches de control -->
            <div class="form-group row mb-4">
                <div class="col-md-6 switch-container">
                    <label class="font-weight-bold" for="switchvencimiento">Fecha vencimiento <span class="text-danger">*</span></label>
                    <InputSwitch id="switchvencimiento" v-model="fechaVencimientoSeleccion" class="p-inputtext-sm" />
                </div>
                <div v-show="tipoAccion == 1" class="col-md-6 switch-container">
                    <label class="font-weight-bold" for="switchstock">Agregar a Stock <span class="text-danger">*</span></label>
                    <InputSwitch id="switchstock" v-model="agregarStock" class="p-inputtext-sm" />
                </div>
            </div>

            <!-- Décima fila: Campos de Stock (condicional) -->
            <div v-if="agregarStock && tipoAccion == 1" class="form-group row mb-4">
                <div class="col-md-4">
                    <label class="font-weight-bold" for="cantidadStock">Cantidad Stock <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputNumber 
                            id="cantidadStock" 
                            v-model="unidadStock" 
                            placeholder="Ej: 10" 
                            class="p-inputtext-sm" 
                            mode="decimal"
                            :class="{'p-invalid' : erroresinventario.unidadStock}" 
                            @input="validarCampoInventario('unidadStock')"
                        />
                    </div>
                    <small class="p-error" v-if="erroresinventario.unidadStock"><strong>{{ erroresinventario.unidadStock }}</strong></small>
                </div>
                <div class="col-md-4">
                    <label class="font-weight-bold" for="fechavencimiento">Fecha de Vencimiento <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <Calendar 
                            v-if="fechaVencimientoSeleccion == false" 
                            id="fechavencimiento" 
                            v-model="fechaPorDefecto" 
                            placeholder="dd/mm/yy" 
                            class="p-inputtext-sm" 
                            :touchUI="true" 
                            :hideOnDateTimeSelect="false" 
                            dateFormat="yy-mm-dd" 
                            disabled
                        />
                        <Calendar 
                            v-if="fechaVencimientoSeleccion == true" 
                            id="fechavencimiento" 
                            v-model="fechaVencimientoAlmacen" 
                            placeholder="dd/mm/yy" 
                            :touchUI="true" 
                            :minDate="minDate" 
                            @date-select="handleDateChange" 
                            class="p-inputtext-sm" 
                            dateFormat="dd/mm/yy" 
                        />
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="font-weight-bold" for="almacen">Almacen <span class="text-danger">*</span></label>
                    <div class="p-inputgroup">
                        <InputText 
                            id="almacen" 
                            v-model="almacenSeleccionado.nombre_almacen" 
                            placeholder="Seleccione un almacen" 
                            class="form-control p-inputtext-sm bold-input" 
                            disabled
                            :class="{'p-invalid' : erroresinventario.AlmacenSeleccionado}" 
                        />
                        <Button label="..." class="p-button-primary p-button-sm" @click="abrirDialogos('Almacen')" />
                    </div>
                    <small class="p-error" v-if="erroresinventario.AlmacenSeleccionado"><strong>{{ erroresinventario.AlmacenSeleccionado }}</strong></small>
                </div>
            </div>

            <!-- Lista de precios -->
            <div v-for="(precio, index) in precios" :key="precio.id" class="form-group row mb-4">
                <div class="col-12">
                    <label class="font-weight-bold">{{ precio.nombre_precio }}:</label>
                </div>
                <div class="col-md-6">
                    <div class="p-inputgroup">
                        <InputNumber 
                            v-if="index === 0" 
                            v-model="precio_uno" 
                            placeholder="Precio" 
                            mode="decimal" 
                            :minFractionDigits="2" 
                            :maxFractionDigits="2" 
                            class="p-inputtext-sm w-full"
                        />
                        <InputNumber 
                            v-if="index === 1" 
                            v-model="precio_dos" 
                            placeholder="Precio" 
                            mode="decimal" 
                            :minFractionDigits="2" 
                            :maxFractionDigits="2" 
                            class="p-inputtext-sm w-full"
                        />
                        <InputNumber 
                            v-if="index === 2" 
                            v-model="precio_tres" 
                            placeholder="Precio" 
                            mode="decimal" 
                            :minFractionDigits="2" 
                            :maxFractionDigits="2" 
                            class="p-inputtext-sm w-full"
                        />
                        <InputNumber 
                            v-if="index === 3" 
                            v-model="precio_cuatro" 
                            placeholder="Precio" 
                            mode="decimal" 
                            :minFractionDigits="2" 
                            :maxFractionDigits="2" 
                            class="p-inputtext-sm w-full"
                        />
                        <span class="p-inputgroup-addon">{{ monedaPrincipal[1] }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-inputgroup">
                        <InputNumber 
                            placeholder="Porcentaje" 
                            v-model="precio.porcentage" 
                            mode="decimal" 
                            :minFractionDigits="2" 
                            class="p-inputtext-sm"
                        />
                        <span class="p-inputgroup-addon">%</span>
                        <Button 
                            label="Calcular" 
                            class="p-button-primary p-button-sm"
                            @click="calcularPrecio(precio, index)" 
                        />
                    </div>
                </div>
            </div>
        </form>

        <!-- Footer con botones de acción -->
        <template #footer>
            <div class="flex justify-content-end gap-2">
                <Button 
                    label="Cerrar" 
                    icon="pi pi-times" 
                    class="p-button-danger p-button-sm" 
                    @click="cerrarModal" 
                />
                <Button 
                    v-if="tipoAccion == 1" 
                    class="p-button-success p-button-sm" 
                    label="Guardar" 
                    icon="pi pi-check" 
                    @click="enviarFormulario()" 
                />
                <Button 
                    v-if="tipoAccion == 2" 
                    class="p-button-warning p-button-sm" 
                    label="Actualizar" 
                    icon="pi pi-check" 
                    @click="enviarFormulario()" 
                />
            </div>
        </template>
    </Dialog>
</template>

        <!-- MODALES DINÁMICOS -->
        <DialogProveedores v-if="mostrarDialogoProveedores" :visible.sync="mostrarDialogoProveedores" @close="cerrarDialogos('Proveedores')" @proveedor-seleccionado="manejarProveedorSeleccionado" />
        <DialogLineas v-if="mostrarDialogoLineas" :visible.sync="mostrarDialogoLineas" @close="cerrarDialogos('Lineas')" @linea-seleccionado="manejarLineaSeleccionado"/>
        <DialogMarcas v-if="mostrarDialogoMarcas" :visible.sync="mostrarDialogoMarcas" @close="cerrarDialogos('Marcas')" @marca-seleccionado="manejarMarcaSeleccionado"/>
        <DialogIndustrias v-if="mostrarDialogoIndustrias" :visible.sync="mostrarDialogoIndustrias" @close="cerrarDialogos('Industrias')" @industria-seleccionado="manejarIndustriaSeleecionado"/>
        <DialogGrupos v-if="mostrarDialogoGrupos" :visible.sync="mostrarDialogoGrupos" @close="cerrarDialogos('Grupos')" @grupo-seleccionado="manejarGrupoSeleccionado"/>
        <DialogMedidas v-if="mostrarDialogoMedidas" :visible.sync="mostrarDialogoMedidas" @close="cerrarDialogos('Medidas')" @medida-seleccionado="manejarMedidaSeleccionado"/>
        <DialogAlmacenes v-if="mostrarDialogoAlmacen" :visible.sync="mostrarDialogoAlmacen" @close="cerrarDialogos('Almacen')" @almacen-seleccionado="manejarAlmacenSeleccionado"/>
        <ImportarExcelNewView v-if="mostrarDialogoImportar" :visible.sync="mostrarDialogoImportar" @cerrar="cerrarDialogos('Importar')"/>

    </main>
</template>

<script>
import Panel from 'primevue/panel';
import Paginator from 'primevue/paginator';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import ImagePreview from 'primevue/imagepreview';
import DialogProveedores from './modales/DialogProveedores.vue';
import DialogLineas from './modales/DialogLineas.vue';
import DialogMarcas from './modales/DialogMarcas.vue';
import DialogIndustrias from './modales/DialogIndustrias.vue';
import DialogGrupos from './modales/DialogGrupos.vue';
import DialogMedidas from './modales/DialogMedidas.vue'
import DialogAlmacenes from './modales/DialogAlmacenes.vue';
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch';
import Calendar from 'primevue/calendar';
import VueBarcode from 'vue-barcode';
import { esquemaArticulos, esquemaInventario } from '../constants/validations';
import ImportarExcelNewView from './productos/ImportarExcelNewView.vue';

export default {
    components: {
        Panel,
        Button,
        InputText,
        Dialog,
        DataTable,
        Column,
        Textarea,
        InputNumber,
        ImagePreview,
        Dropdown,
        InputSwitch,
        Calendar,
        Paginator,
        'barcode': VueBarcode,
        DialogProveedores,
        DialogLineas,
        DialogMarcas,
        DialogIndustrias,
        DialogGrupos,
        DialogMedidas,
        DialogAlmacenes,
        ImportarExcelNewView
    },
    data() {
        return {
            criterio: 'nombre',
            buscar: '',
            arrayArticulo: [], // Datos del artículo
            dialogVisible: false,
            agregarStock: false,
            fechaVencimientoSeleccion: false,
            fechaVencimientoAlmacen : null,
            unidadStock : null,
            datosFormulario: {
                nombre: '',
                descripcion: '',
                nombre_generico: '',
                unidad_envase: 0,
                precio_costo_unid: 0,
                precio_costo_paq: 0,
                precio_venta: 0,
                precio_uno: 0,
                precio_dos: 0,
                precio_tres: 0,
                precio_cuatro: 0,
                stock: 0,
                costo_compra: 0,
                codigo: '',
                codigo_alfanumerico: '',
                descripcion_fabrica: '',
                idcategoria: null,
                idmarca: null,
                idindustria: null,
                idgrupo: null,
                idproveedor: null,
                idmedida: null,
                

            },
            datosFormularioInventario : {
                AlmacenSeleccionado : null ,
                fechaVencimientoAlmacen : null,
                unidadStock : null
            },
            errores: {},
            erroresinventario :{},
            tipo_stock: '',
            tipoEnvase: [
                { valor: "paquetes", etiqueta: "Paquetes" },
                { valor: "unidades", etiqueta: "Unidades" }
            ],
            mostrarDialogoProveedores: false,
            mostrarDialogoLineas: false,
            mostrarDialogoMarcas: false,
            mostrarDialogoIndustrias: false,
            mostrarDialogoGrupos: false,
            mostrarDialogoMedidas: false,
            mostrarDialogoAlmacen: false,
            mostrarDialogoImportar: false,
            proveedorSeleccionado: [],
            lineaSeleccionado: [],
            marcaSeleccionado: [],
            industriaSeleccionado : [],
            grupoSeleccionado: [],
            medidaSeleccionado: [],
            almacenSeleccionado: [],
            precios: [],
            precio_uno: null,
            precio_dos: null,
            precio_tres: null,
            precio_cuatro: null,
            monedaPrincipal: [],

            //CONFIGURACIONES
            mostrarSaldosStock: '',
            mostrarProveedores: '',
            mostrarCostos: '',
            rolUsuario: '',
            articulo_id: 0,
            idcategoria: 0,
            idmarca: 0,
            idindustria: 0,
            idproveedor: 0,
            idgrupo: 0,
            codigoProductoSin: 0,
            idmedida: 0,
            nombreLinea: '',
            nombre_categoria: '',
            nombre_proveedor: '',
            //id:'',//aumente 7 julio
            codigo: '',
            nombre: '',
            nombre_producto: '',
            nombre_generico: '',
            nombreProductoVacio: false,
            codigoVacio: false,
            unidad_envaseVacio: false,
            nombre_genericoVacio: false,
            precio_costo_unidVacio: false,
            precio_costo_paqVacio: false,
            precio_ventaVacio: false,
            costo_compraVacio: false,
            stockVacio: false,
            descripcionVacio: false,
            fotografiaVacio: false,
            lineaseleccionadaVacio: false,
            marcaseleccionadaVacio: false,
            industriaseleccionadaVacio: false,
            proveedorseleccionadaVacio: false,
            gruposeleccionadaVacio: false,
            medidaseleccionadaVacio: false,
            unidad_envase: 0,
            precio_costo_unid: 0,
            precio_costo_paq: 0,
            fotoMuestra: null,
            tipoAccion: 0,
            minDate: null,
            idarticulo: 0,
            pagination: {
                'total': 0,
                'current_page': 1,
                'per_page': 10,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            headers: [
                { field: 'acciones', header: 'Acciones', type: 'button' },
                { field: 'codigo', header: 'CODIGO' },
                { field: 'nombre', header: 'NOMBRE COMERCIAL' },
          
                { field: 'unidad_envase', header: 'UNIDADES POR PAQUETE' },
               
                { field: 'precio_costo_paq', header: 'COSTO PAQUETE' },
                // Las columnas dinámicas se insertarán aquí
                { field: 'nombre_categoria', header: 'LINEA' },
                { field: 'nombre_industria', header: 'INDUSTRIA' },
                { field: 'nombre_marca', header: 'MARCA' },
                { field: 'stock', header: 'STOCK MINIMO' },
                { field: 'nombre_proveedor', header: 'PROVEEDOR' },
                { field: 'descripcion', header: 'DESCRIPCION' },
                { field: 'nombre_grupo', header: 'GRUPO/FAMILIA' },
                { field: 'fotografia', header: 'FOTOGRAFIA', type: 'image' }
            ]
        };
    },
    computed:{
        imagen() {
            return this.fotoMuestra;
        },
        paginatedItems() {
            // Obtener elementos a mostrar en la página actual
            return this.arrayArticulo.slice(this.pagination.from - 1, this.pagination.to);
        },
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

        fechaPorDefecto() {
            let defaultDate;
            if (!this.fechaVencimientoSeleccion) {
                defaultDate = new Date(2099, 11, 31); // 31/12/2099
                this.fechaVencimientoAlmacen = '2099-12-31'; // Formato YYYY-MM-DD
            } else {
                defaultDate = new Date();
                this.fechaVencimientoAlmacen = this.formatDateToYMD(defaultDate); // Formato YYYY-MM-DD
            }
            return defaultDate;
        },
        computedColumns() {
            const dynamicColumns = this.precios.map((precio, index) => ({
                field: `precio_${['uno', 'dos', 'tres', 'cuatro'][index]}`,
                header: ` ${precio.nombre_precio}`,
                type: 'dynamicPrice'
            }));
            const index = this.headers.findIndex(header => header.field === 'precio_costo_paq') + 1;
            const result = [
                ...this.headers.slice(0, index),
                ...dynamicColumns,
                ...this.headers.slice(index)
            ];
            console.log("RESULTS COMPUTED ",index)
            return result;
        }
    },
    methods: {
       // Funciones para el manejo de artículos
       registrarArticulo(data) {
    let me = this;
    var formulario = new FormData();
    for (var key in data) {
        if (data.hasOwnProperty(key)) {
            formulario.append(key, data[key]);
        }
    }

    return axios.post('/articulo/registrar', formulario, {
        headers: {
            'Content-Type': 'multipart/form-data' 
        }
    }).then(function (response) {
        var respuesta = response.data;
        me.idarticulo = respuesta.idArticulo;
        console.log("respuesta = ", me.idarticulo);
        me.cerrarModal();
        me.listarArticulo(1, '', 'nombre');
        me.toastSuccess("Artículo registrado correctamente");
        
        if (me.agregarStock == true) {
            let arrayArticulos = [
                {
                    idarticulo: me.idarticulo,
                    idalmacen: me.almacenSeleccionado.id,
                    cantidad: me.unidadStock,
                    fecha_vencimiento: me.fechaVencimientoAlmacen
                }
            ];
            console.log("registrar inventario", arrayArticulos);
            return axios.post('/inventarios/registrar', { inventarios: arrayArticulos });
        }
    }).then(function (response) {
        if (response && me.agregarStock) {
            console.log(response.data);
            me.toastSuccess("Inventario registrado correctamente");
        }
    }).catch(function (error) {
        console.error(error);
        me.toastError("Hubo un error al registrar el artículo o inventario");
    });
},

actualizarArticulo(data) {
    var formulario = new FormData();
    let me = this;
    for (var key in data) {
        if (data.hasOwnProperty(key)) {
            formulario.append(key, data[key]);
        }
    }

    // Mantenemos POST ya que el backend espera este método
    axios.post('/articulo/actualizar', formulario, {
        headers: {
            'Content-Type': 'multipart/form-data' 
        }
    }).then(function (response) {
        var respuesta = response.data;
        console.log("respuesta actualización = ", respuesta);
        me.cerrarModal();
        me.listarArticulo(1, '', 'nombre');
        me.toastSuccess("Articulo actualizado correctamente");
    }).catch(function (error) {
        console.error("Error al actualizar:", error);
        me.toastError("No se pudo actualizar el articulo");
    });
},

asignarCampos() {
    // Datos comunes para creación y actualización
    this.datosFormulario = {
        nombre: this.nombre,
        codigo: this.codigo,
        idcategoria: this.categoriaSeleccionada ? this.categoriaSeleccionada.id : null,
        idmarca: this.marcaSeleccionada ? this.marcaSeleccionada.id : null,
        idindustria: this.industriaSeleccionada ? this.industriaSeleccionada.id : null,
        idgrupo: this.grupoSeleccionado ? this.grupoSeleccionado.id : null,
        idproveedor: this.proveedorSeleccionado ? this.proveedorSeleccionado.id : null,
        idmedida: this.medidaSeleccionada ? this.medidaSeleccionada.id : null,
        descripcion: this.descripcion,
        precio_venta: this.precio_venta,
        stock: this.stock,
        unidad_envase: this.unidad_envase,
        fechaVencimientoSeleccion: this.fechaVencimientoSeleccion,
        codigo_alfanumerico: this.codigo_alfanumerico,
        descripcion_fabrica: this.descripcion_fabrica,

        precio_costo_unid: this.precio_costo_unid,
        precio_costo_paq: this.precio_costo_paq,
        costo_compra: this.costo_compra
    };
    
    // Si estamos en modo edición, asegurarnos de incluir el ID
    if (this.tipoAccion == 2 && this.articulo && this.articulo.id) {
        this.datosFormulario.id = this.articulo.id;
        console.log("Modo edición: ID del artículo incluido =", this.datosFormulario.id);
    }
},

asignarCamposPrecios() {
    // Asignar precios al formulario
    this.datosFormulario.precio_uno = this.precio_uno;
    this.datosFormulario.precio_dos = this.precio_dos;
    this.datosFormulario.precio_tres = this.precio_tres;
    this.datosFormulario.precio_cuatro = this.precio_cuatro;
},

asignarCamposInventario() {
    // Solo para creación con stock
    this.datosFormularioInventario = {
        unidadStock: this.unidadStock,
        almacenSeleccionado: this.almacenSeleccionado ? this.almacenSeleccionado.id : null,
        fechaVencimientoAlmacen: this.fechaVencimientoAlmacen
    };
},
async enviarFormulario() {
    try {
        // 1. Preparar datos
        this.asignarCampos();
        this.asignarCamposPrecios();
        
        console.log("Datos formulario:", JSON.parse(JSON.stringify(this.datosFormulario)));
        
        // 2. Validación artículo
        let validacionArticuloExitosa = true;
        try {
            await esquemaArticulos.validate(this.datosFormulario, { abortEarly: false });
            this.errores = {};
        } catch (error) {
            validacionArticuloExitosa = false;
            this.mapearErroresValidacion(error, 'errores');
        }

        // 3. Si hay errores en artículo, detener aquí
        if (!validacionArticuloExitosa) {
            this.toastError("Corrija los errores en el formulario");
            return;
        }

        // 4. Procesar imagen
        this.datosFormulario.fotografia = this.fotografia;

        // 5. Registrar o actualizar
        if (this.tipoAccion == 2) {
            await this.actualizarArticulo(this.datosFormulario);
        } else {
            const response = await this.registrarArticulo(this.datosFormulario);
            
            // Solo si se registró correctamente y hay que agregar stock
            if (this.agregarStock && response && response.data.idArticulo) {
                await this.registrarInventario(response.data.idArticulo);
            }
        }
    } catch (error) {
        console.error("Error en enviarFormulario:", error);
        this.toastError("Error al procesar el artículo");
    }
},

// Nuevo método auxiliar para registrar inventario
async registrarInventario(idArticulo) {
    try {
        const inventarioData = {
            idarticulo: idArticulo,
            idalmacen: this.almacenSeleccionado.id,
            cantidad: this.unidadStock || 0,
            fecha_vencimiento: this.fechaVencimientoAlmacen
        };
        
        await axios.post('/inventarios/registrar', { inventarios: [inventarioData] });
        this.toastSuccess("Inventario registrado correctamente");
    } catch (error) {
        console.error("Error al registrar inventario:", error);
        this.toastError("Artículo registrado pero falló inventario");
    }
},

// Método para mapear errores de validación
mapearErroresValidacion(error, target) {
    const errores = {};
    error.inner.forEach((e) => {
        errores[e.path] = e.message;
        console.error(`Error validación ${e.path}: ${e.message}`);
    });
    this[target] = errores;
},

// Método para abrir el modal según el tipo de acción (1=Nuevo, 2=Editar)
abrirModal(tipoAccion, articulo = null) {
    this.tipoAccion = tipoAccion;
    this.tituloModal = tipoAccion === 1 ? 'Nuevo Artículo' : 'Editar Artículo';
    this.errores = {};
    this.erroresinventario = {};
    
    if (tipoAccion === 1) {
        // Nuevo artículo - reiniciar valores
        this.reiniciarValores();
    } else {
        // Edición - cargar datos del artículo
        this.articulo = articulo;
        this.cargarDatosArticulo(articulo);
    }
    
    this.modal = true;
},

// Método para cargar datos del artículo en edición
cargarDatosArticulo(articulo) {
    this.id = articulo.id;
    this.nombre = articulo.nombre;
    this.codigo = articulo.codigo;
    this.descripcion = articulo.descripcion;
    this.precio_venta = articulo.precio_venta;
    this.stock = articulo.stock;
    this.unidad_envase = articulo.unidad_envase;
    this.precio_uno = articulo.precio_uno;
    this.precio_dos = articulo.precio_dos;
    this.precio_tres = articulo.precio_tres;
    this.precio_cuatro = articulo.precio_cuatro;
    this.precio_costo_unid = articulo.precio_costo_unid;
    this.precio_costo_paq = articulo.precio_costo_paq;
    this.costo_compra = articulo.costo_compra;
    this.codigo_alfanumerico = articulo.codigo_alfanumerico;
    this.descripcion_fabrica = articulo.descripcion_fabrica;
    
    this.fechaVencimientoSeleccion = articulo.vencimiento;
    
    // Cargar datos de relaciones (categoría, marca, etc.)
    this.cargarSelecciones(articulo);
    
    // Si hay una imagen, cargarla
    if (articulo.fotografia) {
        this.imagenPreview = '/img/articulo/' + articulo.fotografia;
        this.fotografia = null; // Resetear la fotografia para no reemplazarla automáticamente
    }
},

// Cargar selecciones de dropdowns para edición
cargarSelecciones(articulo) {
    // Buscar y asignar categoría
    if (articulo.idcategoria) {
        this.categoriaSeleccionada = this.categorias.find(c => c.id === articulo.idcategoria) || null;
    }
    
    // Buscar y asignar marca
    if (articulo.idmarca) {
        this.marcaSeleccionada = this.marcas.find(m => m.id === articulo.idmarca) || null;
    }
    
    // Y así con el resto de selecciones...
    // Industria
    if (articulo.idindustria) {
        this.industriaSeleccionada = this.industrias.find(i => i.id === articulo.idindustria) || null;
    }
    
    // Grupo
    if (articulo.idgrupo) {
        this.grupoSeleccionado = this.grupos.find(g => g.id === articulo.idgrupo) || null;
    }
    
    // Proveedor
    if (articulo.idproveedor) {
        this.proveedorSeleccionado = this.proveedores.find(p => p.id === articulo.idproveedor) || null;
    }
    
    // Medida
    if (articulo.idmedida) {
        this.medidaSeleccionada = this.medidas.find(m => m.id === articulo.idmedida) || null;
    }
},

cerrarModal() {
    this.modal = false;
    this.reiniciarValores();
},

reiniciarValores() {
    // Reinicia todos los campos del formulario
    this.id = null;
    this.nombre = '';
    this.codigo = '';
    this.descripcion = '';
    this.precio_venta = 0;
    this.stock = 0;
    this.unidad_envase = 1;
    // ... reiniciar todos los demás campos
    this.imagenPreview = null;
    this.fotografia = null;
    this.errorFotografia = null;
    
    // Reiniciar selecciones
    this.categoriaSeleccionada = null;
    this.marcaSeleccionada = null;
    this.industriaSeleccionada = null;
    this.grupoSeleccionado = null;
    this.proveedorSeleccionado = null;
    this.medidaSeleccionada = null;
    
    // Reiniciar errores
    this.errores = {};
    this.erroresinventario = {};
},


        toastSuccess(mensaje) {
            this.$toasted.show(`
    <div style="height: 60px;font-size:16px;">
        <br>
        `+ mensaje + `.<br>
    </div>`, {
                type: "success",
                position: "bottom-right",
                duration: 4000
            });
        },
        toastError(mensaje) {
            this.$toasted.show(`
    <div style="height: 60px;font-size:16px;">
        <br>
        `+ mensaje + `<br>
    </div>`, {
                type: "error",
                position: "bottom-right",
                duration: 4000
            });
        },
        handleDateChange(date) {
            // Verifica si date es un objeto Date válido
            if (date instanceof Date && !isNaN(date)) {
                this.fechaVencimientoAlmacen = this.formatDateToYMD(date);
                console.log("fecha ",this.fechaVencimientoAlmacen)
            } else {
                console.error('La fecha seleccionada no es válida:', date);
            }
        },
        formatDateToYMD(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Los meses son indexados desde 0
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        closeDialog() {
            this.dialogVisible = false;
        },
        obtenerFotografia(event) {

            let file = event.target.files[0];

            let fileType = file.type;
            // Validar si el archivo es una imagen en formato PNG o JPG
            if (fileType !== 'image/png' && fileType !== 'image/jpeg') {
                alert('Por favor, seleccione una imagen en formato PNG o JPG.');
                return;
            }

            this.fotografia = file;
            this.mostrarFoto(file);
        },
        mostrarFoto(file) {
            let reader = new FileReader();
            reader.onload = (file) => {
                this.fotoMuestra = file.target.result;
            }
            reader.readAsDataURL(file);
        },
        manejarProveedorSeleccionado(proveedor) {
            this.proveedorSeleccionado = proveedor;
            this.validarCampo("idproveedor");
        },
        manejarLineaSeleccionado(linea){
            this.lineaSeleccionado = linea;
            this.validarCampo("idcategoria");
        },
        manejarMarcaSeleccionado(marca){
            this.marcaSeleccionado = marca;
            this.validarCampo("idmarca");
        },
        manejarIndustriaSeleecionado(industria){
            this.industriaSeleccionado = industria;
            this.validarCampo("idindustria");
        },
        manejarGrupoSeleccionado(grupo){
            this.grupoSeleccionado = grupo;
            this.validarCampo("idgrupo");

        },
        manejarMedidaSeleccionado(medida){
            this.medidaSeleccionado = medida;
            this.validarCampo("idmedida");
        },
        manejarAlmacenSeleccionado(almacen){
            this.almacenSeleccionado = almacen;
            if(this.agregarStock == true){
                this.validarCampoInventario("AlmacenSeleccionado");
            }

        },
        abrirDialogos(dialogo){
            switch (dialogo){
                case 'Proveedores':
                    this.mostrarDialogoProveedores = true;
                    break;
                case 'Lineas':
                    this.mostrarDialogoLineas = true;
                    break;
                case 'Marcas':
                    this.mostrarDialogoMarcas = true;
                    break;
                case 'Industrias':
                    this.mostrarDialogoIndustrias = true;
                    break;
                case 'Grupos':
                    this.mostrarDialogoGrupos = true;
                    break;
                case 'Medidas':
                    this.mostrarDialogoMedidas = true;
                    break;
                case 'Almacen':
                    this.mostrarDialogoAlmacen = true;
                    this.dialogVisible= false;
                    break;
                case 'Importar':
                    this.mostrarDialogoImportar = true;
                    break;
            }
            this.dialogVisible = false;
        }, 
        cerrarDialogos(dialogo) {
            switch (dialogo) {
                case 'Proveedores':
                    this.mostrarDialogoProveedores = false;
                    break;
                case 'Lineas':
                    this.mostrarDialogoLineas = false;
                    break;
                case 'Marcas':
                    this.mostrarDialogoMarcas = false;
                    break;
                case 'Industrias':
                    this.mostrarDialogoIndustrias = false;
                    break;
                case 'Grupos':
                    this.mostrarDialogoGrupos = false;
                    break;
                case 'Medidas':
                    this.mostrarDialogoMedidas = false;
                    break;
                case 'Almacen':
                    this.mostrarDialogoAlmacen = false;
                    break;
                case 'Importar':
                    this.mostrarDialogoImportar = false;
                    this.listarArticulo(1, '', 'nombre');
                    break;
            }
            this.dialogVisible = true;
        }, 
        listarPrecio() {
            let me = this;
            var url = '/precios';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.precios = respuesta.precio.data;
                //me.precioCount = me.arrayBuscador.length;
            }).catch(function (error) {
                console.log(error);
            });
        },
        buscarArticulo(){
            this.listarArticulo(1,this.buscar)
        },
        asignarCampos() {
            this.datosFormulario.idcategoria = this.lineaSeleccionado.id
            this.datosFormulario.idmarca = this.marcaSeleccionado.id
            this.datosFormulario.idproveedor = this.proveedorSeleccionado.id
            this.datosFormulario.idindustria = this.industriaSeleccionado.id
            this.datosFormulario.idmedida = this.medidaSeleccionado.id
            this.datosFormulario.idgrupo = this.grupoSeleccionado.id
            
            if(this.fechaVencimientoSeleccion == false){
                this.datosFormulario.fechaVencimientoSeleccion = '0'
            }
            else{
                this.datosFormulario.fechaVencimientoSeleccion = '1'
            }
        },
        asignarCamposPrecios(){
            this.datosFormulario.precio_costo_unid = this.convertDolar(this.datosFormulario.precio_costo_unid);
            this.datosFormulario.precio_costo_paq = this.convertDolar(this.datosFormulario.precio_costo_paq);
            this.datosFormulario.precio_venta = this.convertDolar(this.datosFormulario.precio_venta);

            this.datosFormulario.precio_uno = this.convertDolar(this.precio_uno);
            this.datosFormulario.precio_dos = this.convertDolar(this.precio_dos);
            this.datosFormulario.precio_tres = this.convertDolar(this.precio_tres);
            this.datosFormulario.precio_cuatro = this.convertDolar(this.precio_cuatro);
            this.datosFormulario.costo_compra = this.convertDolar(this.datosFormulario.costo_compra);
        },
        asignarCamposInventario(){
            this.datosFormularioInventario.AlmacenSeleccionado = this.almacenSeleccionado.id
            this.datosFormularioInventario.unidadStock = this.unidadStock;
            this.datosFormularioInventario.fechaVencimientoAlmacen = this.fechaVencimientoAlmacen;
        },
        convertDolar(precio) {
            return (precio / parseFloat(this.monedaPrincipal))
        },
        async validarCampo(campo) {
            this.asignarCampos();
            try {
                await esquemaArticulos.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },
        async validarCampoInventario(campo){
            this.asignarCamposInventario();
            try {
                await esquemaInventario.validateAt(campo, this.datosFormularioInventario);
                this.erroresinventario[campo] = null;
            } catch (error) {
                this.erroresinventario[campo] = error.message;
            }
        },
    
        obtenerConfiguracionTrabajo() {
            // Utiliza Axios para realizar la solicitud al backend
            axios.get('/configuracion')
                .then(response => {
                    console.log(response)
                })
                .catch(error => {
                    console.error('Error al obtener configuración de trabajo:', error);
                });
        },
        datosConfiguracion() {
            let me = this;
            var url = '/configuracion';

            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.mostrarSaldosStock = respuesta.configuracionTrabajo.mostrarSaldosStock;
                me.mostrarCostos = respuesta.configuracionTrabajo.mostrarCostos;
                me.mostrarProveedores = respuesta.configuracionTrabajo.mostrarProveedores;

                me.monedaPrincipal = [respuesta.configuracionTrabajo.valor_moneda_principal, respuesta.configuracionTrabajo.simbolo_moneda_principal]

            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        calculatePages: function (paginationObject, offset) {
            if (!paginationObject.to) {
                return [];
            }

            var from = paginationObject.current_page - offset;
            if (from < 1) {
                from = 1;
            }

            var to = from + (offset * 2);
            if (to >= paginationObject.last_page) {
                to = paginationObject.last_page;
            }

            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        calcularPrecioCostoUnid() {
            if (this.datosFormulario.unidad_envase && this.datosFormulario.precio_costo_paq) {
                this.datosFormulario.precio_costo_unid = this.datosFormulario.precio_costo_paq / this.datosFormulario.unidad_envase;
                this.datosFormulario.precio_costo_unidVacio = false;
                this.validarCampo('precio_costo_unid');
            }
        },
        calcularPrecioCostoPaq() {
            if (this.datosFormulario.unidad_envase && this.datosFormulario.precio_costo_unid) {
                this.datosFormulario.precio_costo_paq = this.datosFormulario.precio_costo_unid * this.datosFormulario.unidad_envase;
                this.datosFormulario.precio_costo_paqVacio = false;
                this.validarCampo('precio_costo_paq');
            }
        },
        calcularPrecioP(precio_costo_unid, porcentage) {
            const margenG = precio_costo_unid * (porcentage / 100);
            const precioP = parseFloat(precio_costo_unid) + parseFloat(margenG);
            return precioP.toFixed(2);
        },
        listarArticulo(page, buscar, criterio) {
            let me = this;
            var url = '/articulo?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayArticulo = respuesta.articulos.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        cambiarPagina(page, buscar, criterio) {
            // Actualiza la página actual
            this.pagination.current_page = page;
            // Envía la petición para visualizar la data de esa página
            this.listarArticulo(page, buscar, criterio);
        },
        onPageChange(event) {
            const page = Math.floor(event.first / this.pagination.per_page) + 1;
            this.cambiarPagina(page, '', ''); // Ajusta los parámetros según tu lógica
        },
        cargarPdf() {
            window.open('/articulo/listarPdf', '_blank');
        },
        cambiarPagina(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            //Envia la petición para visualizar la data de esa página
            me.listarArticulo(page, buscar, criterio);
        },
        calcularPrecioValorMoneda(precio) {
            return Number((precio * parseFloat(this.monedaPrincipal)).toFixed(2));
        },
  
        desactivarArticulo(id) {
            swal({
                title: 'Esta seguro de desactivar este artículo?',
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

                    axios.put('/articulo/desactivar', {
                        'id': id
                    }).then(function (response) {
                        me.listarArticulo(1, '', 'nombre');
                        swal(
                            'Desactivado!',
                            'El registro ha sido desactivado con éxito.',
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
        activarArticulo(id) {
            swal({
                title: 'Esta seguro de activar este artículo?',
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

                    axios.put('/articulo/activar', {
                        'id': id
                    }).then(function (response) {
                        me.listarArticulo(1, '', 'nombre');
                        swal(
                            'Activado!',
                            'El registro ha sido activado con éxito.',
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
        validarArticulo() {
            this.errorArticulo = 0;
            this.errorMostrarMsjArticulo = [];
            if (!this.unidad_envase) this.errorMostrarMsjArticulo.push("");
            if (!this.codigo) this.errorMostrarMsjArticulo.push("");
            if (!this.nombre_producto) this.errorMostrarMsjArticulo.push("");
            if (!this.nombre_generico) this.errorMostrarMsjArticulo.push("");
            if (!this.precio_costo_unid) this.errorMostrarMsjArticulo.push("");
            if (!this.precio_costo_paq) this.errorMostrarMsjArticulo.push("");
            if (!this.descripcion) this.errorMostrarMsjArticulo.push("");
            if (!this.stock) this.errorMostrarMsjArticulo.push("");
            if (!this.precio_venta) this.errorMostrarMsjArticulo.push("");
            if (!this.costo_compra) this.errorMostrarMsjArticulo.push("");
            if (!this.fotografia) this.errorMostrarMsjArticulo.push("");

            if (this.errorMostrarMsjArticulo.length) this.errorArticulo = 1;

            return this.errorArticulo;
        },
        cerrarModal() {
            this.dialogVisible = false;
            this.tituloModal = '';
            this.codigo = '';
            this.nombre_producto = '';
            this.nombre_generico = '';
            this.precio_venta = '';
            this.precio_costo_unid = '';
            this.precio_costo_paq = '';
            this.stock = '';
            this.descripcion = '';
            this.fotografia = ''; //Pasando el valor limpio de la referencia
            this.fotoMuestra = null;
            this.lineaSeleccionado=[];
            this.marcaSeleccionado=[];
            this.industriaSeleccionado=[];
            this.proveedorSeleccionado=[];
            this.grupoSeleccionado=[];
            this.medidaSeleccionado=[];
            this.fechaVencimientoSeleccion = false;
            this.errorArticulo = 0;
            this.idmedida = 0;
            this.costo_compra = '';
            this.precio_uno = '';
            this.precio_dos = '';
            this.precio_tres = '';
            this.precio_cuatro = '';
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "articulo":
                    {
                        switch (accion) {

                            case 'registrar':
                                {
                                    this.dialogVisible = true;
                                    this.tituloModal = 'Registrar Artículo';
                                    this.agregarStock=false;
                                    this.tipoAccion = 1;
                                    this.fotografia = '';

                                    this.datosFormulario = {
                                        nombre: '',
                                        descripcion: '',
                                      
                                        unidad_envase: null,
                                        precio_costo_unid: null,
                                        precio_costo_paq: null,
                                        precio_venta: null,
                                        precio_uno: null,
                                        precio_dos: null,
                                        precio_tres: null,
                                        precio_cuatro: null,
                                        stock: null,
                                        costo_compra: null,
                                        codigo: '',
                                        codigo_alfanumerico: '',
                                        descripcion_fabrica: '',
                                        idcategoria: null,
                                        idmarca: null,
                                        idindustria: null,
                                        idgrupo: null,
                                        idproveedor: null,
                                        idmedida: null
                                    };
                                    this.errores = {};
                                    break;
                                }
                            case 'actualizar':
                                {
                                    console.log("DATA ACTUALIZAR",data)
                                    this.agregarStock=false;
                                    this.dialogVisible = true;
                                    this.tituloModal = 'Actualizar Artículo';
                                    this.tipoAccion = 2;
                                    this.datosFormulario = {
                                        nombre: data['nombre'],
                                        descripcion: data['descripcion'],
                                        nombre_generico: data['nombre_generico'],
                                        unidad_envase: data['unidad_envase'],
                                        precio_costo_unid: this.calcularPrecioValorMoneda(data['precio_costo_unid']),
                                        precio_costo_paq: this.calcularPrecioValorMoneda(data['precio_costo_paq']),
                                        precio_venta: this.calcularPrecioValorMoneda(data['precio_venta']),
                                        precio_uno: 0,
                                        precio_dos: 0,
                                        precio_tres: 0,
                                        precio_cuatro: 0,
                                        stock: this.tipo_stock == "paquetes" ? data['stock'] / data['unidad_envase'] : data['stock'],
                                        costo_compra: this.calcularPrecioValorMoneda(data['costo_compra']),
                                        codigo: data['codigo'],
                                        codigo_alfanumerico: data['codigo_alfanumerico'] ? data['codigo_alfanumerico'] : "",
                                        descripcion_fabrica: data['descripcion_fabrica'] ? data['descripcion_fabrica'] : "",
                                        idcategoria: null,
                                        idmarca: null,
                                        idindustria: null,
                                        idgrupo: null,
                                        idproveedor: null,
                                        idmedida: data['idmedida'],
                                        id: data['id']
                                    };
                                    this.errores = {};
                                    this.idmedida = data['idmedida'];

                                    this.fotografia = data['fotografia'];
                                    this.fotoMuestra = data['fotografia'] ? 'img/articulo/' + data['fotografia'] : null;
                                    //this.industriaseleccionada = { nombre: data['industriaseleccionada.nombre'] };

                                    //this.industriaseleccionada = {nombre: data['nombre_industria']};
                                    this.industriaSeleccionado = { nombre: data['nombre_industria'], id: data['idindustria'] };
                                    //this.lineaseleccionada = {nombre: data['nombre_categoria']};
                                    this.lineaSeleccionado = { nombre: data['nombre_categoria'], id: data['idcategoria'] };
                                    //this.marcaseleccionada = {nombre: data['nombre_marca']};
                                    this.marcaSeleccionado = { nombre: data['nombre_marca'], id: data['idmarca'] };
                                    this.proveedorSeleccionado = { nombre: data['nombre_proveedor'], id: data['idproveedor'] };
                                    //this.gruposeleccionada = {nombre_grupo: data['nombre_grupo']};
                                    this.grupoSeleccionado = { nombre_grupo: data['nombre_grupo'], id: data['idgrupo'] };
                                    this.medidaSeleccionado = { descripcion_medida: data['descripcion_medida'], id: data['idmedida'] };

                                    this.precio_uno = this.calcularPrecioValorMoneda(data['precio_uno']);
                                    this.precio_dos = this.calcularPrecioValorMoneda(data['precio_dos']);
                                    this.precio_tres = this.calcularPrecioValorMoneda(data['precio_tres']);
                                    this.precio_cuatro = this.calcularPrecioValorMoneda(data['precio_cuatro']);
                                    // this.precios.forEach((precio) => {
                                    //     this.calcularPrecio(precio);
                                    // });
                                    this.fechaVencimientoSeleccion =  data['vencimiento'] === 1 ? true : false;
                                    break;

                                }
                            case 'registrarInd':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Registrar Industria';
                                    this.nombre = '';
                                    //this.descripcion = '';
                                    this.tipoAccion = 3;
                                    break;
                                }
                        }
                    }
            }
        },
        calcularPrecio(precio, index) {
            if (isNaN(this.datosFormulario.precio_costo_unid) || isNaN(parseFloat(precio.porcentage))) {
                return;
            }
            const margen_ganancia = parseFloat(this.datosFormulario.precio_costo_unid) * (parseFloat(precio.porcentage) / 100);
            const precio_publico = parseFloat(this.datosFormulario.precio_costo_unid) + margen_ganancia;
            console.log("precio publico",typeof precio_publico);
            if (index === 0) {
                this.precio_uno = Number(parseFloat(precio_publico).toFixed(2));
            } else if (index === 1) {
                this.precio_dos = Number(parseFloat(precio_publico).toFixed(2));
            } else if (index === 2) {
                this.precio_tres = Number(parseFloat(precio_publico).toFixed(2));
            } else if (index === 3) {
                this.precio_cuatro = Number(parseFloat(precio_publico).toFixed(2));
            }
        },
        recuperarIdRol() {
            this.rolUsuario = window.userData.rol;
            
        },

    },

    mounted() {
        this.recuperarIdRol();
        this.datosConfiguracion();
        this.obtenerConfiguracionTrabajo();
        this.listarArticulo(1, this.buscar, this.criterio);
        this.listarPrecio();//aumenTe 6julio
        let today = new Date();
        let tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        this.minDate = tomorrow;
    }
};
</script>

<style scoped>
>>>.p-datatable.p-datatable-gridlines .p-datatable-tbody > tr > td {
    text-align: center;
}
.bold-input {
    font-weight: bold;
}
.panel-header {
    display: flex;
    align-items: center;
}
.panel-title {
    margin-left: 8px;
}
.toolbar-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}
.search-bar {
  flex-grow: 0.5;
  display: flex;
  align-items: center;    
  justify-content: flex-start;
}
.search-bar .p-input-icon-left {
  width: 100%;
}
.search-bar .p-inputtext-sm {
  width: 100%;
}
.form-group {
    margin-bottom: 15px;
}
.p-dialog-mask {
  z-index: 9999 !important;
}
.p-dialog {
  z-index: 10000 !important;
}
>>> .p-dropdown .p-dropdown-trigger {
    width: 2rem;
}
.switch-container {
    display: flex;
    align-items: center;
    justify-content: space-evenly;
}
.custom-precios{
    display: flex;
    justify-content: space-evenly;
    align-items: center;
}
@media (max-width: 768px) {
    .toolbar-container {
    flex-direction: column;
    align-items: flex-start;
    }
    .toolbar {
        margin-bottom: 10px;
        justify-content: space-between;
    }
    .searchbar {
        margin-bottom: 10px;
        order: 1; /* Esto asegura que la barra de búsqueda esté abajo en vista móvil */
    }
  }
</style>
