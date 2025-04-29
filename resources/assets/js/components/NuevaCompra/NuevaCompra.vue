<template>
    <main class="main">
        <Panel>
            <Toast :breakpoints="{'920px': {width: '100%', right: '0', left: '0'}}" style="padding-top: 40px;"></Toast>
            <template #header>
                <div class="panel-header">
                    <i class="pi pi-shopping-cart panel-icon"></i>
                    <h4 class="panel-icon">Registrar Compra</h4>
                </div>
            </template>

            <template #icons>
                <Button
                    v-if="activeIndex == 1"
                    type="button"
                    @click="toggle"
                    aria-haspopup="true"
                    aria-controls="overlay_menu" 
                    class="p-button-warning p-button-sm"
                    icon="pi pi-cog"
                />
                <Menu id="overlay_menu" ref="menu" :model="items" :popup="true" />
            </template>

            <TabView ref="tabview" :activeIndex="activeIndex">
                <TabPanel :disabled="activeIndex !== 0">
                    <template #header>
                        <div class="titulos-pasos">
                            <Button id="primerPaso" type="button" label="PASO 1" icon="pi pi-bookmark" class="p-button-secondary p-button-rounded non-clickable" />
                            <label for="primerPaso" style="margin-top: 0.25rem;">Selección de Productos</label>
                        </div>
                    </template>

                    <div class="card formulario-card">
                        <Card>
                            <template #content>
                                <div class="p-fluid p-formgrid p-grid">
                                    <div class="p-field p-col-12 p-md-3">
                                        <label for="autocomplete">Proveedor</label>
                                        <div class="autocomplete-flecha">
                                            <AutoComplete
                                                id="autocomplete"
                                                class="p-inputtext-sm"
                                                forceSelection
                                                :dropdown="true"
                                                v-model="form.proveedorSeleccionado" 
                                                :suggestions="array_proveedores" 
                                                field="nombre" 
                                                @complete="selectProveedor($event)" 
                                                placeholder="Buscar Proveedores..."
                                                :class="{'p-invalid': submitted && v$.form.proveedorSeleccionado.$invalid}"
                                            />
                                        </div>
                                        <small class="p-error" v-if="(submitted && v$.form.proveedorSeleccionado.required.$invalid)"><strong>Proveedor es obligatorio.</strong></small>
                                    </div>
                                    <div class="p-field p-col-12 p-md-3">
                                        <label for="tipoComprobante">Tipo Comprobante</label>
                                        <Dropdown id="tipoComprobante" class="p-inputtext-sm" v-model="form.tipo_comprobante" :options="lista_comprobantes" optionLabel="nombre" placeholder="Lista comprobantes ..." :class="{'p-invalid': submitted && v$.form.tipo_comprobante.$invalid}"/>
                                        <small class="p-error" v-if="(submitted && v$.form.tipo_comprobante.required.$invalid)"><strong>Comprobante es obligatorio.</strong></small>
                                    </div>
                                    <div class="p-field p-col-12 p-md-3">
                                        <label for="serieComprobante">Serie Comprobante</label>
                                        <InputText id="serieComprobante" class="p-inputtext-sm" v-model="form.serie_comprobante" placeholder="Serie ..." />
                                    </div>
                                    <div class="p-field p-col-12 p-md-3">
                                        <label for="numeroComprobante">Número Comprobante</label>
                                        <InputText id="numeroComprobante" class="p-inputtext-sm" v-model="form.num_comprobante" placeholder="Número ..." />
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <div class="p-grid">
                        <div class="p-col-12 p-md-6">
                            <div class="tablas-card">
                                <div class="card">
                                    <DataTable
                                        ref="dt-articulos"
                                        :value="array_articulos_proveedor"
                                        selectionMode="multiple"
                                        dataKey="id"
                                        :selection.sync="array_articulos_seleccionados"
                                        responsiveLayout="scroll"
                                        :paginator="false"
                                        :scrollable="true"
                                        scrollHeight="40vh"
                                        tableStyle="height:40vh"
                                        class="p-datatable-sm"
                                        :metaKeySelection="false"
                                        showGridlines
                                    >
                                        <template #header>
                                            <div class="tablas-articulos-header">
                                                <h5>Selecciona artículos</h5>
                                                <span class="p-input-icon-left">
                                                    <i class="pi pi-search" />
                                                    <InputText style="width: 11rem;" v-model="buscadorArticulos" class="p-inputtext-sm" placeholder="Buscador global" />
                                                </span>
                                            </div>
                                        </template>

                                        <Column selectionMode="multiple" :styles="{'max-width':'10%'}"></Column>
                                        <Column field="codigo" header="Codigo" :sortable="true" :styles="{'max-width':'15%'}"></Column>
                                        <Column field="nombre" header="Nombre" :sortable="true" :styles="{'max-width':'35%'}"></Column>
                                        <Column field="precio_costo_unid" header="Precio Unidad" :sortable="true" :styles="{'max-width':'20%'}">
                                            <template #body="slotProps">
                                                {{ formatCurrency(slotProps.data.precio_costo_unid) }}
                                            </template>
                                        </Column>
                                        <Column field="precio_costo_paq" header="Precio Paquete" :sortable="true" :styles="{'max-width':'20%'}">
                                            <template #body="slotProps">
                                                {{ formatCurrency(slotProps.data.precio_costo_paq) }}
                                            </template>
                                        </Column>

                                        <template #empty>
                                            <div class="imagen-tabla-vacia">
                                                <img src="img/iconos/agregar-carrito.png" alt="Articulo sin foto" class="product-image" />
                                                <h5 style="margin-top: 15px;">Agregue artículos ...</h5>
                                            </div>
                                        </template>
                                    </DataTable>
                                </div>
                            </div>
                        </div>

                        <div class="p-col-12 p-md-6">
                            <div class="tablas-card">
                                <div class="card">
                                    <DataTable
                                        ref="dt-seleccionados"
                                        :value="array_articulos_seleccionados"
                                        dataKey="id"
                                        responsiveLayout="scroll"
                                        :paginator="false"
                                        editMode="row"
                                        :editingRows.sync="editingRows"
                                        @row-edit-init="onRowEditInit"
                                        @row-edit-save="onRowEditSave"
                                        @row-edit-cancel="onRowEditCancel"
                                        class="p-datatable-sm"
                                        :scrollable="true"
                                        scrollHeight="40vh"
                                        tableStyle="height:40vh"
                                        showGridlines
                                        :rowHover="true"
                                    >
                                        <template #header>
                                            <div class="tablas-articulos-header">
                                                <div style="display: flex; justify-content: center;">
                                                    <h5 class="mr-2" style="margin-top: 10px;">Artículos seleccionados</h5>
                                                    <Badge :value="array_articulos_seleccionados.length" severity="warning" class="mr-4" size="large" style="margin-top: 5px;"></Badge>
                                                </div>
                                                <Button type="button" class="p-button-sm p-button-danger" label="Vaciar lista" icon="pi pi-trash" @click="vaciarListaSeleccionados"/>
                                            </div>
                                        </template>

                                        <Column field="codigo" header="Codigo" :sortable="true" :styles="{'max-width':'15%'}"></Column>
                                        <Column field="nombre" header="Nombre" :sortable="true" :styles="{'max-width':'35%'}"></Column>
                                        <Column field="precio_costo_unid" header="Precio Unidad" :sortable="true" :styles="{'max-width':'15%'}">
                                            <template #body="slotProps">
                                                {{ formatCurrency(slotProps.data.precio_costo_unid) }}
                                            </template>
                                            <template #editor="slotProps">
                                                <InputNumber 
                                                    v-model="slotProps.data.precio_costo_unid"
                                                    mode="decimal"
                                                    :min="0"
                                                    :maxFractionDigits="2"
                                                    class="p-inputtext-sm"
                                                />
                                            </template>
                                        </Column>
                                        <Column field="precio_costo_paq" header="Precio Paquete" :sortable="true" :styles="{'max-width':'15%'}">
                                            <template #body="slotProps">
                                                {{ formatCurrency(slotProps.data.precio_costo_paq) }}
                                            </template>
                                            <template #editor="slotProps">
                                                <InputNumber 
                                                    v-model="slotProps.data.precio_costo_paq"
                                                    mode="decimal"
                                                    :min="0"
                                                    :maxFractionDigits="2"
                                                    class="p-inputtext-sm"
                                                />
                                            </template>
                                        </Column>
                                        <Column :rowEditor="true" :styles="{'max-width':'10%'}" :bodyStyle="{'text-align':'center'}"></Column>

                                        <Column :styles="{'max-width': '10%'}" :bodyStyle="{'text-align': 'center', overflow: 'visible'}">
                                            <template #body="slotProps">
                                                <Button type="button" icon="pi pi-delete-left" class="p-button-danger p-button-sm" @click="eliminarArticuloSeleccionado(slotProps.data)"></Button>
                                            </template>
                                        </Column>

                                        <template #empty>
                                            <div class="imagen-tabla-vacia">
                                                <img src="img/iconos/venta-express.png" alt="Articulo sin foto" class="product-image" />
                                                <h5 style="margin-top: 15px;">Sin artículos seleccionados ...</h5>
                                            </div>
                                        </template>
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabPanel>

                <TabPanel :disabled="activeIndex !== 1">
                    <template #header>
                        <div class="titulos-pasos">
                            <Button id="segundoPaso" type="button" label="PASO 2" icon="pi pi-bookmark" class="p-button-secondary p-button-rounded non-clickable" />
                            <label for="segundoPaso" style="margin-top: 0.25rem;">Configuración y Pago</label>
                        </div>
                    </template>

                    <div class="card">
                        <div class="tabla-completa">
                            <DataTable
                                ref="dt-lista-completo"
                                :value="array_articulos_completo"
                                dataKey="id"
                                :paginator="true"
                                :rows="3"
                                :expandedRows.sync="expandedRows"
                                @row-expand="onRowExpand"
                                @row-collapse="onRowCollapse"
                                editMode="row"
                                :editingRows.sync="editingRows"
                                @row-edit-init="onRowEditInit"
                                @row-edit-save="onRowEditSave"
                                @row-edit-cancel="onRowEditCancel"
                                responsiveLayout="scroll"
                                class="p-datatable-sm"
                                tableStyle="height: 40vh"
                            >
                                <Column :expander="true" :headerStyle="{'width': '5%'}"></Column>
                                <Column field="codigo" header="Codigo" :sortable="true" :styles="{width:'5%'}"></Column>
                                <Column header="Image" :styles="{width:'5%'}">
                                    <template #body="slotProps">
                                        <img v-if="slotProps.data.fotografia == null" src="img/iconos/producto-imagen-default.jpg" alt="Articulo sin foto" class="product-image" />
                                        <img v-else :src="'/img/articulos/' + slotProps.data.fotografia" alt="Articulo foto" class="product-image">
                                    </template>
                                </Column>
                                <Column field="nombre" header="Nombre" :sortable="true" :styles="{width:'25%'}"></Column>
                                <Column field="nombre_proveedor" header="Proveedor" :sortable="true" :styles="{width:'15%'}"></Column>
                                <Column field="unidad_envase" header="Unidades por Paquete" :sortable="true" :styles="{width:'10%'}"></Column>
                                <Column field="precio_costo_unid" header="Costo Unidad" :sortable="true" :styles="{width:'15%'}">
                                    <template #body="slotProps">
                                        {{ formatCurrency(slotProps.data.precio_costo_unid) }}
                                    </template>
                                    <template #editor="slotProps">
                                        <InputNumber 
                                            v-model="slotProps.data.precio_costo_unid"
                                            mode="decimal"
                                            :min="0"
                                            :maxFractionDigits="2"
                                            class="p-inputtext-sm"
                                        />
                                    </template>
                                </Column>
                                <Column field="precio_costo_paq" header="Costo Paquete" :sortable="true" :styles="{width:'15%'}">
                                    <template #body="slotProps">
                                        {{ formatCurrency(slotProps.data.precio_costo_paq) }}
                                    </template>
                                    <template #editor="slotProps">
                                        <InputNumber 
                                            v-model="slotProps.data.precio_costo_paq"
                                            mode="decimal"
                                            :min="0"
                                            :maxFractionDigits="2"
                                            class="p-inputtext-sm"
                                        />
                                    </template>
                                </Column>
                                <Column :rowEditor="true" :styles="{'width': '5%'}" :bodyStyle="{'text-align':'center'}"></Column>
                                <Column :headerStyle="{'width': '5%','min-width':'8rem'}" :bodyStyle="{'text-align': 'center', overflow: 'visible'}">
                                    <template #body="slotProps">
                                        <Button type="button" icon="pi pi-delete-left" class="p-button-danger p-button-sm" @click="eliminarArticuloListaCompleta(slotProps.data)"></Button>
                                    </template>
                                </Column>

                                <template #empty>
                                    <div class="imagen-tabla-vacia">
                                        <img src="img/iconos/carrito-de-compras-final.png" alt="Articulo sin foto" class="product-image" />
                                        <h5 style="margin-top: 15px;">Sin artículos seleccionados ...</h5>
                                    </div>
                                </template>

                                <template #expansion="slotProps">
                                    <div class="orders-subtable">
                                        <div style="display: flex; justify-content: center; background: #a2acb9; color: #ffffff; padding-top: 7px;">
                                            <h6><strong>Informacion adicional</strong></h6>
                                        </div>
                                        <div class="tabla-expansible">
                                            <DataTable :value="[slotProps.data]" responsiveLayout="scroll">
                                                <Column header="Fecha Vencimiento" :styles="{width:'10%'}">
                                                    <template #body="slotProps">
                                                        <div class="p-inputgroup">
                                                            <Button
                                                                type="button"
                                                                icon="pi pi-calendar"
                                                                class="p-button-sm"
                                                                @click="toggleFechaVencimiento(slotProps.data)"
                                                                :class="{'p-button-secondary': !slotProps.data.fecha_vencimiento, 'p-button-success': slotProps.data.fecha_vencimiento}"
                                                            />
                                                            <input
                                                                v-if="slotProps.data.fecha_vencimiento"
                                                                type="date"
                                                                v-model="slotProps.data.fecha_vencimiento"
                                                                class="p-inputtext-sm"
                                                            />
                                                            <span v-else class="p-inputtext p-component p-inputtext-sm" style="width: 100%;">Opcional</span>
                                                        </div>
                                                    </template>
                                                </Column>

                                                <Column field="nombre_categoria" header="Categoria" :styles="{width:'15%'}"></Column>
                                                <Column :header="slotProps.data.esPaquetesCantidad ? ' Cantidad en: Paquetes' : 'Cantidad en: Unidades'" :styles="{width:'20%'}" style="padding-top: 40px !important;">
                                                    <template #body="slotProps">
                                                        <div class="p-inputgroup">
                                                            <Button
                                                                type="button"
                                                                :label="slotProps.data.esPaquetesCantidad ? 'Paquetes' : 'Unidades'"
                                                                @click="toggleUnidadesPaquetesCantidad(slotProps.data)"
                                                                icon="pi pi-bell"
                                                                class="p-button-sm p-button-secondary"
                                                                :class="{'espacio-top': slotProps.data.unidades <= 0}"
                                                            />
                                                            <InputNumber
                                                                class="p-inputtext-sm"
                                                                v-model="slotProps.data.unidades"
                                                                mode="decimal"
                                                                :step="1"
                                                                showButtons
                                                                :min="0"
                                                                decrementButtonClass="p-button-danger p-button-sm"
                                                                incrementButtonClass="p-button-sm"
                                                                incrementButtonIcon="pi pi-plus"
                                                                decrementButtonIcon="pi pi-minus"
                                                                @input="updateSubtotal(slotProps.data)"
                                                                :class="{
                                                                    'invalido': slotProps.data.unidades <= 0,
                                                                    'espacio-top': slotProps.data.unidades <= 0
                                                                }"
                                                            />
                                                        </div>
                                                        <small class="p-error" v-if="slotProps.data.unidades <= 0">Cantidad inválida</small>
                                                    </template>
                                                </Column>
                                                <Column :header="slotProps.data.esPaquetesBonificacion ? 'Bonificacion en: Paquetes' : 'Bonificacion en: Unidades'" :styles="{width:'20%'}">
                                                    <template #body="slotProps">
                                                        <div class="p-inputgroup">
                                                            <Button
                                                                type="button"
                                                                :label="slotProps.data.esPaquetesBonificacion ? 'Paquetes' : 'Unidades'"
                                                                @click="toggleUnidadesPaquetesBonificacion(slotProps.data)"
                                                                icon="pi pi-bell"
                                                                class="p-button-sm p-button-secondary"
                                                            />
                                                            <InputNumber
                                                                class="p-inputtext-sm"
                                                                v-model="slotProps.data.bonificacion"
                                                                mode="decimal"
                                                                :step="1"
                                                                showButtons
                                                                :min="0"
                                                                decrementButtonClass="p-button-danger p-button-sm"
                                                                incrementButtonClass="p-button-sm"
                                                                incrementButtonIcon="pi pi-plus"
                                                                decrementButtonIcon="pi pi-minus"
                                                                @input="updateUnidadesTotales(slotProps.data)"
                                                            />
                                                        </div>
                                                    </template>
                                                </Column>
                                                <Column field="descuento" header="Descuento" :styles="{width:'10%'}">
                                                    <template #body="slotProps">
                                                        <InputNumber
                                                            class="p-inputtext-sm"
                                                            v-model="slotProps.data.descuento"
                                                            placeholder="% 0"
                                                            prefix="% "
                                                            mode="decimal"
                                                            :maxFractionDigits="2"
                                                            :min="0"
                                                            @input="updateSubtotal(slotProps.data)"
                                                            inputStyle="width:7rem;"
                                                            :class="{
                                                                'invalido': verificarDescuento(slotProps.data),
                                                                'espacio-top': verificarDescuento(slotProps.data)
                                                            }"
                                                        />
                                                        <small class="p-error" v-if="verificarDescuento(slotProps.data)">Descuento inválido</small>
                                                    </template>
                                                </Column>
                                                <Column header="Subtotal" :styles="{width:'10%'}">
                                                    <template #body="slotProps">
                                                        <InputNumber
                                                            class="p-inputtext-sm"
                                                            disabled
                                                            :value="calculateSubtotal(slotProps.data)"
                                                            mode="decimal"
                                                            inputStyle="width:7rem;"
                                                        />
                                                    </template>
                                                </Column>

                                                <template #empty>
                                                    Datos del articulo no encontrados ...
                                                </template>
                                            </DataTable>
                                        </div>
                                    </div>
                                </template>

                                <template #paginatorend>
                                    <label for="descuentoGlobal" style="padding-right: 10px;">Descuento Global</label>
                                    <InputNumber id="descuentoGlobal" class="p-inputtext-sm" v-model="descuentoGlobal" prefix="% " mode="decimal" :maxFractionDigits="2" :max="100" :min="0" inputStyle="width:7rem;"/>
                                </template>
                            </DataTable>
                        </div>
                    </div>

                    <div class="total-saldo-card">
                        <Card>
                            <template #content>
                                <div class="saldo-total"><strong>TOTAL Bs. {{ saldoTotalCompra }}</strong></div>
                            </template>
                        </Card>
                    </div>

                    <div class="card">
                        <div class="p-fluid p-formgrid p-grid">
                            <div class="p-field p-col-12 p-md-3">
                                <label for="nombreUsuario">Nombre Usuario</label>
                                <div class="p-inputgroup">
                                    <span class="p-inputgroup-addon">
                                        <i class="pi pi-user"></i>
                                    </span>    
                                    <InputText id="nombreUsuario" placeholder="Nombre usuario actual ..." v-model="nombreUsuario" disabled class="p-inputtext-sm"/>
                                </div>
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="tipoCompra">Tipo de Compra</label>
                                <Dropdown id="tipoCompra" v-model="tipoCompra" :options="lista_tipo_compra" optionLabel="nombre" placeholder="Tipo de compra ..." class="p-inputtext-sm" :class="{'p-invalid': submitted && v$.tipoCompra.$invalid}"/>
                                <small class="p-error" v-if="(submitted && v$.tipoCompra.required.$invalid)"><strong>Tipo de Compra es obligatorio.</strong></small>
                            </div>

                            <div class="p-field p-col-12 p-md-3">
                                <label for="autocomplete">Almacén</label>
                                <div class="autocomplete-flecha">
                                    <AutoComplete
                                        id="autocomplete"
                                        class="p-inputtext-sm"
                                        forceSelection
                                        v-model="almacenSeleccionado" 
                                        :suggestions="array_almacenes" 
                                        field="nombre_almacen" 
                                        @complete="buscarAlmacen"
                                        placeholder="Buscar Almacén ..."
                                        :class="{'p-invalid': submitted && v$.almacenSeleccionado.$invalid}"
                                    />
                                </div>
                                <small class="p-error" v-if="(submitted && v$.almacenSeleccionado.required.$invalid)"><strong>Almacén de destino es obligatorio.</strong></small>
                            </div>

                            <div class="p-field p-col-12 p-md-3" v-if="!verificarCompraContado">
                                <label for="botonComprarContado" style="padding-bottom: 17px;"></label>
                                <Button id="botonComprarContado" icon="pi pi-check-square" label="Registrar Compra" class="p-button-success p-button-sm" @click="registrarCompra" />
                            </div>
                            <div class="p-field p-col-12 p-md-3" v-else>
                                <label for="botonComprarCredito" style="padding-bottom: 17px;"></label>
                                <Button id="botonComprarCredito" icon="pi pi-check-square" label="Compra a Credito" class="p-button-help p-button-sm" @click="openComprasCredito" />
                            </div>
                        </div>
                    </div>
                </TabPanel>
            </TabView>

            <div class="flechas-buttons">
                <Button class="p-button-sm p-button-secondary" icon="pi pi-chevron-left" label="Anterior" @click="prevStep" :disabled="activeIndex === 0" style="margin-right: 30px;"/>
                <Button class="p-button-sm p-button-secondary" icon="pi pi-chevron-right" label="Siguiente" iconPos="right" @click="nextStep" :disabled="activeIndex === steps.length - 1" />
            </div>
        </Panel>

        <Dialog header="Confirmacion" :visible.sync="displayConfirmation" :containerStyle="{width: '350px'}" :modal="true">
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>¿Seguro de cambiar el precio del articulo?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" @click="closeConfirmation" class="p-button-sm p-button-danger"/>
                <Button label="Si" icon="pi pi-check" @click="actualizarCostosArticulo" class="p-button-sm p-button-success" autofocus />
            </template>
        </Dialog>

        <div class="dialog-cuotas">
            <Dialog header="Compra a Credito" :visible.sync="displayCompraCredito" :modal="true" @hide="closeComprasCredito" :containerStyle="{width: '65vw'}">
                <div class="card">
                    <div class="p-fluid p-formgrid p-grid">
                        <div class="p-field p-col-12 p-md-4">
                            <label for="cantidadCuotas">Cantidad de Cuotas</label>
                            <InputNumber class="p-inputtext-sm" id="cantidadCuotas" placeholder="0 cuotas" v-model="form_cuotas.num_cuotas" suffix=" cuotas" :min="0" :class="{'p-invalid': submitted && v$.form_cuotas.num_cuotas.$invalid}"/>
                            <small class="p-error" v-if="(submitted && v$.form_cuotas.num_cuotas.required.$invalid)"><strong>Cantidad es obligatorio.</strong></small>
                            <small class="p-error" v-if="(submitted && v$.form_cuotas.num_cuotas.minValueValue.$invalid)"><strong>No puede ser 0.</strong></small>
                        </div>

                        <div class="p-field p-col-12 p-md-4">
                            <label for="frecuenciaPago">Frecuencia de Pago</label>
                            <InputNumber class="p-inputtext-sm" id="frecuenciaPago" placeholder="0 dias" v-model="form_cuotas.frecuencia_pagos" suffix=" dias" :min="0" :class="{'p-invalid': submitted && v$.form_cuotas.frecuencia_pagos.$invalid}"/>
                            <small class="p-error" v-if="(submitted && v$.form_cuotas.frecuencia_pagos.required.$invalid)"><strong>Frecuencia es obligatorio.</strong></small>
                            <small class="p-error" v-if="(submitted && v$.form_cuotas.frecuencia_pagos.minValueValue.$invalid)"><strong>No puede ser 0.</strong></small>
                        </div>

                        <div class="p-field p-col-12 p-md-4">
                            <label for="generarCuotas"><strong>TOTAL Bs. {{ saldoTotalCompra }}</strong></label>
                            <Button class="p-button-success p-button-sm" label="Generar Cuota" icon="pi pi-clock" @click="generarCuotas"/>
                        </div>
                    </div>

                    <div class="p-fluid p-formgrid p-grid">
                        <div class="p-field p-col-12 p-md-4">
                            <label for="cuotaInicial">Cuota Inicial</label>
                            <InputNumber
                                class="p-inputtext-sm"
                                id="cuota_inicial"
                                placeholder="Bs 0"
                                v-model="form_cuotas.cuota_inicial"
                                mode="decimal"
                                :min="0"
                                prefix="Bs "
                                :maxFractionDigits="2"
                                :class="{'p-invalid': submitted && v$.form_cuotas.cuota_inicial.$invalid}"
                            />
                            <small class="p-error" v-if="(submitted && v$.form_cuotas.cuota_inicial.required.$invalid)"><strong>Cuota Inicial es obligatorio.</strong></small>
                            <small class="p-error" v-if="(submitted && v$.form_cuotas.cuota_inicial.minValueValue.$invalid)"><strong>No puede ser 0.</strong></small>
                        </div>

                        <div class="p-field p-col-12 p-md-4">
                            <label for="tipoPagoCuota">Tipo de Pago</label>
                            <Dropdown class="p-inputtext-sm" v-model="form_cuotas.tipoPagoCuotaSeleccionado" :options="lista_tipo_pago_cuotas" optionLabel="nombre" placeholder="Selecciona el tipo de pago" :class="{'p-invalid': submitted && v$.form_cuotas.tipoPagoCuotaSeleccionado.$invalid}"/>
                            <small class="p-error" v-if="(submitted && v$.form_cuotas.tipoPagoCuotaSeleccionado.required.$invalid)"><strong>Tipo Pago es obligatorio.</strong></small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <DataTable
                        ref="dt-cuotas"
                        :value="array_cuotas_calculadas"
                        :paginator="false"
                        dataKey="id"
                        class="p-datatable-sm"
                        :rowHover="true"
                        responsiveLayout="scroll"
                        :scrollable="true"
                        scrollHeight="37vh"
                        tableStyle="height:37vh"
                        showGridlines
                    >
                        <template #empty>
                            <div class="imagen-tabla-vacia">
                                <img src="img/iconos/lista-cuotas.png" alt="Cuotas vacias" style="width: 100px;">
                                <h5 style="padding-top: 15px;">Sin cuotas generadas ...</h5>
                            </div> 
                        </template>

                        <Column field="id" header="#" ></Column>
                        <Column field="fecha_pago" header="Fecha Pago" ></Column>
                        <Column field="precio_cuota" header="Precio Cuota" ></Column>
                        <Column field="total_cancelado" header="Total Cancelado" ></Column>
                        <Column field="saldo_restante" header="Saldo Restante" ></Column>
                        <Column field="fecha_cancelado" header="Fecha Cancelado" ></Column>
                        <Column header="Estado">
                            <template #body="slotProps">
                                <Tag
                                    v-if="slotProps.data.estado == 'Cuota Inicial'"
                                    class="mr-2"
                                    severity="danger"
                                    :value="slotProps.data.estado"
                                />
                                <Tag
                                    v-else
                                    class="mr-2"
                                    severity="warning"
                                    :value="slotProps.data.estado"
                                />
                            </template>
                        </Column>

                        <template #paginatorend>
                            <Button label="Registrar Compra" icon="pi pi-check-square" @click="registrarCompra" class="p-button-sm p-button-help" autofocus />
                        </template>
                    </DataTable>
                </div>

                <template #footer>
                    <Button label="Cancelar" icon="pi pi-times" @click="closeComprasCredito" class="p-button-sm p-button-danger"/>
                    <Button label="Registrar Compra" icon="pi pi-check-square" @click="registrarCompra" class="p-button-sm p-button-help" />
                </template>
            </Dialog>
        </div>
    </main>
</template>


<script src="./NuevaCompra.js"/>

<style scoped src="./NuevaCompra.css" />
