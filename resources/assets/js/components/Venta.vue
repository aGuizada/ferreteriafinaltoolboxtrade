<template>
    <main class="main">
        <!-- Breadcrumb -->
        <Panel>
            <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
            </Toast>
            <template #header>
                <div class="panel-header">

                    <h4 class="panel-icon">Ventas</h4>
                </div>
            </template>
            <!-- Buscador -->
          <div class="p-d-flex p-ai-center p-mb-4">
  <Button @click="abrirTipoVenta" label="Nueva" icon="pi pi-plus" class="p-button-success-sm p-mr-2"/>
  <span class="p-input-icon-left p-input-icon-right p-w-100">
    <i class="pi pi-search" />
    <InputText v-model="buscar" @input="buscarVenta" placeholder="Buscar venta..." 
               class="p-inputtext-lg-sm moto-search p-w-100" />
    <i class="pi pi-times" v-if="buscar" @click="
      buscar = '';
      buscarVenta();
    " style="cursor: pointer" />
  </span>
</div>

            <!-- Listado de Ventas -->
            <template v-if="listado == 1">
                <DataTable :value="arrayVenta" :rows="10" responsiveLayout="scroll"
                    class="p-datatable-gridlines p-datatable-sm moto-table" :rowHover="true" dataKey="id">
                    <Column header="Opciones">
                        <template #body="slotProps">
                            <Button icon="pi pi-eye" @click="verVenta(slotProps.data.id)" class="p-button-sm p-mr-1"
                                style="
                  background-color: green;
                  border-color: green;
                  color: white;
                " />
                            <template v-if="slotProps.data.estado === 'Registrado' && idrol !== 2">
                                <Button icon="pi pi-trash" @click="desactivarVenta(slotProps.data.id)"
                                    class="p-button-sm p-button-danger p-mr-1" />
                            </template>
                            <Button icon="pi pi-print" @click="
                                imprimirResivo(slotProps.data.id, slotProps.data.correo)
                                " class="p-button-sm p-button-primary p-mr-1" />
                        </template>
                    </Column>
                    <Column field="usuario" header="Vendedor"></Column>
                    <Column field="razonSocial" header="Cliente"></Column>
                    <Column field="documentoid" header="Documento" class="d-none d-md-table-cell"></Column>
                    <Column field="num_comprobante" header="N° de Comprobante" class="d-none d-md-table-cell"></Column>
                    <Column field="fecha_hora" header="Fecha y Hora" class="d-none d-md-table-cell"></Column>
                    <Column header="Total">
                        <template #body="slotProps">
                            <span class="moto-price">
                                {{
                                    (slotProps.data.total * parseFloat(monedaVenta[0])).toFixed(2)
                                }}
                                {{ monedaVenta[1] }}
                            </span>
                        </template>
                    </Column>
                    <Column field="estado" header="Estado" class="d-none d-md-table-cell"></Column>
                </DataTable>

                <Paginator :rows="10" :totalRecords="pagination.total" :first="(pagination.current_page - 1) * 10"
                    @page="onPageChange" />
            </template>

            <!-- Ver Detalle de Venta -->
            <template v-else-if="listado == 2">
                <Card class="shadow">
                    <template #content>
                        <div class="p-grid p-fluid border p-3 mb-3">
                            <div class="p-col-12 p-md-9">
                                <div class="p-field">
                                    <label>Cliente</label>
                                    <InputText v-model="cliente" disabled />
                                </div>
                            </div>
                            <div class="p-col-12 p-md-3">
                                <div class="p-field">
                                    <label>Tipo Comprobante</label>
                                    <InputText v-model="tipo_comprobante" disabled />
                                </div>
                            </div>
                            <div class="p-col-12 p-md-3">
                                <div class="p-field">
                                    <label>Número Comprobante</label>
                                    <InputText v-model="num_comprobante" disabled />
                                </div>
                            </div>
                        </div>
                        <DataTable :value="arrayDetalle" class="mb-3" responsiveLayout="scroll">
                            <Column field="articulo" header="Artículo"></Column>
                            <Column header="Precio">
                                <template #body="slotProps">
                                    {{
                                        (
                                            slotProps.data.precio * parseFloat(monedaVenta[0])
                                        ).toFixed(2)
                                    }}
                                    {{ monedaVenta[1] }}
                                </template>
                            </Column>
                            <Column field="cantidad" header="Cantidad"></Column>
                            <Column header="Subtotal">
                                <template #body="slotProps">
                                    {{
                                        (
                                            slotProps.data.precio *
                                            slotProps.data.cantidad *
                                            parseFloat(monedaVenta[0])
                                        ).toFixed(2)
                                    }}
                                    {{ monedaVenta[1] }}
                                </template>
                            </Column>
                        </DataTable>
                        <div class="p-text-right p-mb-3">
                            <strong>Total Neto:
                                {{ (total * parseFloat(monedaVenta[0])).toFixed(2) }}
                                {{ monedaVenta[1] }}</strong>
                        </div>
                        <div class="p-text-right">
                            <Button label="Cerrar" @click="ocultarDetalle()" class="p-button-secondary" />
                        </div>
                    </template>
                </Card>
            </template>
        </Panel>
        <!-- HASTA AQUI DEVOLUCIONES -->
        <template>
            <Dialog :visible.sync="modal2" :containerStyle="{ width: '100%', maxWidth: '850px' }" :modal="true"
                :closable="true" :closeOnEscape="false" @hide="cerrarModal2">
                <template #header>
                    <h5 class="modal-title">DETALLE VENTAS</h5>
                </template>
                <div class="p-fluid">
                    <div class="p-field">
                        <div class="step-indicators">
                            <span :class="['step', { active: step === 1, completed: step > 1 }]">1</span>
                            <span :class="['step', { active: step === 2, completed: step > 2 }]">2</span>
                            <span :class="['step', { active: step === 3 }]">3</span>
                        </div>
                    </div>
                    <div v-if="step === 1" class="step-content p-fluid">
                        <template>
                            <div class="form-container">
                                <div class="form-row">
                                    <div class="form-group">
                                        <div class="p-inputgroup">
                                            <span class="p-inputgroup-addon">
                                                <i class="pi pi-id-card"></i>
                                            </span>
                                            <span class="p-float-label">
                                                <InputText id="documento" v-model="documento" @input="checkDocumento"
                                                    maxlength="7" class="w-full" />
                                                <label for="documento">Numero de Documento</label>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="p-inputgroup">
                                            <span class="p-inputgroup-addon">
                                                <i class="pi pi-user"></i>
                                            </span>
                                            <span class="p-float-label">
                                                <InputText id="nombreCliente" v-model="nombreCliente"
                                                    :disabled="!nombreClienteEditable" class="w-full" />
                                                <label for="nombreCliente">Nombre del cliente</label>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <span class="p-float-label">
                                            <Dropdown id="tipoComprobante" v-model="tipo_comprobante"
                                                :options="tipoComprobanteOptions" optionLabel="name" optionValue="code"
                                                class="w-full" />
                                            <label for="tipoComprobante">Comprobante <span
                                                    class="p-error">*</span></label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <InputText v-model="idcliente" type="hidden" />
                        <InputText v-model="tipo_documento" type="hidden" />
                        <InputText v-model="complemento_id" type="hidden" />
                        <InputText v-model="usuarioAutenticado" type="hidden" />
                        <InputText v-model="puntoVentaAutenticado" type="hidden" />
                        <InputText v-model="email" type="hidden" />
                        <InputText v-model="num_comprob" type="hidden" disabled />
                    </div>

                    <div v-if="step === 2" class="step-content">
                        <!-- Header Section -->
                        <div class="p-fluid p-grid">
                            <div class="p-col-12 p-md-6">
                                <label class="p-d-block">Almacen <span class="p-error">*</span></label>
                                <Dropdown v-model="selectedAlmacen" :options="arrayAlmacenes"
                                    optionLabel="nombre_almacen" optionValue="id" placeholder="Seleccione un almacén"
                                    @change="getAlmacenProductos" />
                            </div>

                            <div class="p-col-12 p-md-6">
                                <label class="p-d-block">Buscar articulo</label>
                                <div class="p-inputgroup">
                                    <InputText v-model="codigo" placeholder="Codigo del articulo"
                                        :disabled="!selectedAlmacen" @keyup="buscarArticulo()" />
                                    <Button icon="pi pi-search" :disabled="!selectedAlmacen" @click="abrirModal" />
                                </div>
                            </div>
                        </div>

                        <!-- Product Details Section -->
                        <div v-if="arraySeleccionado && Object.keys(arraySeleccionado).length > 0
                        " class="p-mt-4">
                            <template>
                                <Card class="product-card">
                                    <template #content>
                                        <div class="p-grid">
                                            <h2 class="product-title p-col-12 p-mb-3">
                                                {{ arraySeleccionado.nombre }}
                                            </h2>
                                            <div class="p-col-12 p-md-6 p-lg-6">
                                                <div class="product-image-container">
                                                    <img :src="arraySeleccionado.fotografia
                                                        ? `img/articulo/${arraySeleccionado.fotografia}?t=${new Date().getTime()}`
                                                        : 'img/productoSinImagen.png'
                                                        " :alt="arraySeleccionado.nombre" class="product-image" />
                                                </div>
                                            </div>
                                            <div class="p-col-12 p-md-6 p-lg-6">
                                                <div class="stock-info p-mb-3">
                                                    <i :class="stockMostrado > 0 ? 'pi pi-check-circle' : 'pi pi-exclamation-triangle'"
                                                        :style="{ color: stockMostrado > 0 ? 'var(--green-500)' : 'var(--yellow-500)' }"></i>
                                                    <span>{{ stockMostrado > 0 ? "En stock" : "Bajo stock" }}</span>
                                                    <strong>
                                                        {{ stockMostrado }}
                                                        {{ unidadPaquete === 'paquete' ? 'Paquetes' : 'Unidades' }}
                                                        disponibles
                                                    </strong>
                                                </div>
                                                <div class="product-price-section">
                                                    <h4 class="price-title">PRECIOS:</h4>
                                                    <div class="product-price p-mb-4">
                                                        <div v-for="(precio, key) in [
                                                            { nombre: 'Precio Uno', valor: arraySeleccionado.precio_uno },
                                                            { nombre: 'Precio Dos', valor: arraySeleccionado.precio_dos },
                                                            { nombre: 'Precio Tres', valor: arraySeleccionado.precio_tres },
                                                            { nombre: 'Precio Cuatro', valor: arraySeleccionado.precio_cuatro },
                                                            { nombre: 'Precio Venta', valor: arraySeleccionado.precio_venta }
                                                        ]" :key="key" class="precio-opcion"
                                                            v-if="precio.valor !== '0.0000'">
                                                            <input type="radio" :id="key" :value="precio.valor"
                                                                v-model="precioSeleccionado"
                                                                @change="seleccionarPrecio(precio.valor)">
                                                            <label :for="key">
                                                                {{ precio.nombre }}: {{ formatearPrecio(precio.valor) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <h4>Precio:</h4>
                                                    <h2 class="selected-price" v-if="precioSeleccionado">
                                                        {{ formatearPrecio(precioSeleccionado) }}
                                                    </h2>
                                                    <h2 class="selected-price" v-else>
                                                        Ningún precio seleccionado
                                                    </h2>
                                                </div>
                                                <div class="purchase-options">
                                                    <div class="p-grid p-fluid">
                                                        <div class="p-col-6">
                                                            <div class="p-field">
                                                                <label>Tipo de venta</label>
                                                                <Dropdown v-model="unidadPaquete"
                                                                    :options="tipoVentaOptions" optionLabel="label"
                                                                    optionValue="value" class="w-full"
                                                                    @change="actualizarVistaStock" />
                                                            </div>
                                                        </div>
                                                        <div class="p-col-6">
                                                            <div class="p-field">
                                                                <label>Cantidad</label>
                                                                <div class="p-inputgroup">
                                                                    <InputNumber v-model="cantidad" :min="1" showButtons
                                                                        buttonLayout="horizontal"
                                                                        decrementButtonClass="p-button-danger"
                                                                        incrementButtonClass="p-button-success"
                                                                        incrementButtonIcon="pi pi-plus"
                                                                        decrementButtonIcon="pi pi-minus" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="p-col-12">
                                                            <div class="p-d-flex p-jc-center p-mt-3">
                                                                <Button label="Agregar" icon="pi pi-shopping-cart"
                                                                    @click="agregarDetalle"
                                                                    class="p-button-success p-mr-2" />
                                                                <Button label="Eliminar" icon="pi pi-trash"
                                                                    @click="eliminarSeleccionado"
                                                                    class="p-button-danger" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </template>
                        </div>

                        <!-- Cart Table -->
                        <div class="p-mt-4">
                            <DataTable :value="arrayDetalle" class="p-mt-3">
                                <Column header="Opciones" style="width: 10%">
                                    <template #body="slotProps">
                                        <Button icon="pi pi-trash" class="p-button-danger p-button-sm" @click="
                                            slotProps.data.medida != 'KIT'
                                                ? eliminarDetalle(slotProps.data.id)
                                                : eliminarKit(slotProps.data.idkit)
                                            " />
                                    </template>
                                </Column>
                                <Column field="articulo" header="Artículo" style="width: 30%" />
                                <Column field="precioUnidad" header="Precio Unidad" style="width: 15%">
                                    <template #body="slotProps">
                                        {{
                                            (
                                                slotProps.data.precioseleccionado *
                                                parseFloat(monedaVenta[0])
                                            ).toFixed(2)
                                        }}
                                        {{ monedaVenta[1] }}
                                    </template>
                                </Column>
                                <Column field="unidades" header="Unidades" style="width: 10%">
                                    <template #body="slotProps">
                                        <InputNumber v-model="slotProps.data.cantidad" :min="1" showButtons
                                            buttonLayout="horizontal" decrementButtonClass="p-button-danger p-button-sm"
                                            incrementButtonClass="p-button-success p-button-sm"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus"
                                            @input="actualizarDetalle(slotProps.data.id)" />
                                    </template>
                                </Column>
                                <Column field="total" header="Total" style="width: 20%">
                                    <template #body="slotProps">
                                        {{
                                            (
                                                slotProps.data.precioseleccionado *
                                                slotProps.data.cantidad *
                                                parseFloat(monedaVenta[0])
                                            ).toFixed(2)
                                        }}
                                        {{ monedaVenta[1] }}
                                    </template>
                                </Column>
                            </DataTable>
                        </div>

                        <!-- Total Section -->
                        <div class="p-grid p-mt-3">
                            <div class="p-col-12 p-md-8"></div>
                            <div class="p-col-12 p-md-4 p-text-right">
                                <h3>
                                    Total Neto:
                                    {{ (calcularTotal * parseFloat(monedaVenta[0])).toFixed(2) }}
                                    {{ monedaVenta[1] }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div v-show="step === 3" class="step-content">
                        <template>
                            <div class="p-d-flex p-jc-center p-mb-3">
                                <div v-if="!tipoVentaSeleccionado" class="p-d-flex">
                                    <Button class="p-button-lg p-mr-3"
                                        :class="{ 'p-button-primary': tipoVenta === 'contado', 'p-button-outlined': tipoVenta !== 'contado' }"
                                        @click="seleccionarTipoVenta('contado')">
                                        <template #default>
                                            <div class="p-d-flex p-flex-column p-ai-center">
                                                <i class="pi pi-money-bill p-mr-2" style="font-size: 2rem;"></i>
                                            </div>
                                            <span>Contado</span>
                                        </template>
                                    </Button>
                                    <Button class="p-button-lg"
                                        :class="{ 'p-button-primary': tipoVenta === 'credito', 'p-button-outlined': tipoVenta !== 'credito' }"
                                        @click="seleccionarTipoVenta('credito')">
                                        <template #default>
                                            <div class="p-d-flex p-flex-column p-ai-center">
                                                <i class="pi pi-credit-card p-mb-2" style="font-size: 2rem;"></i>
                                            </div>
                                            <span>Crédito</span>
                                        </template>
                                    </Button>
                                </div>
                                <div v-else class="p-d-flex p-ai-center">
                                    <i :class="tipoVenta === 'contado' ? 'pi pi-money-bill' : 'pi pi-credit-card'"
                                        class="p-mr-2" style="font-size: 2rem;"></i>
                                    <span class="p-text-bold">{{ tipoVenta === 'contado' ? 'Contado' : 'Crédito'
                                        }}</span>
                                </div>
                            </div>
                        </template>
                        <template>
  <div v-if="tipoVenta === 'contado'" class="payment-options">
    <TabView class="custom-tabview">
      <TabPanel header="Efectivo">
        <div class="p-grid p-fluid">
          <div class="p-col-12 p-md-7">
            <Card>
              <template #content>
                <div class="p-fluid">
                  <div class="p-field">
                    <label for="montoEfectivo"><i class="pi pi-money-bill p-mr-2" /> Monto Recibido:</label>
                    <div class="p-inputgroup">
                      <span class="p-inputgroup-addon">{{ monedaVenta[1] }}</span>
                      <InputNumber id="montoEfectivo" v-model="recibido" placeholder="Ingrese el monto recibido" />
                    </div>
                  </div>
                  <div class="p-field">
                    <label for="cambioRecibir"><i class="pi pi-sync p-mr-2" /> Cambio a Entregar:</label>
                    <InputText id="cambioRecibir" :value="(recibido - calcularTotal * parseFloat(monedaVenta[0])).toFixed(2)" readonly />
                  </div>
                </div>
              </template>
            </Card>
          </div>
          <div class="p-col-12 p-md-5">
            <Card>
              <template #content>
                <h5>Detalle de Venta</h5>
                <div class="p-d-flex p-jc-between p-mb-2">
                  <span><i class="pi pi-dollar p-mr-2" /> Monto Total:</span>
                  <span class="p-text-bold">{{ (calcularTotal * parseFloat(monedaVenta[0])).toFixed(2) }} {{ monedaVenta[1] }}</span>
                </div>
                <div class="p-d-flex p-jc-between">
                  <span><i class="pi pi-money-bill p-mr-2" /> Total a Pagar:</span>
                  <span class="p-text-bold p-text-xl">{{ (calcularTotal * parseFloat(monedaVenta[0])).toFixed(2) }} {{ monedaVenta[1] }}</span>
                </div>
              </template>
            </Card>
            <Button label="Registrar Pago" icon="pi pi-check" class="p-button-success p-mt-2 p-button-lg p-button-raised" @click="aplicarDescuento" />
          </div>
        </div>
      </TabPanel>
      
      <TabPanel header="QR">
        <div class="p-grid p-fluid">
          <div class="p-col-12 p-md-8 p-lg-6 p-mx-auto">
            <Card>
              <template #content>
                <div class="form-group">
                  <input v-model="alias" readonly style="display: none" />
                </div>
                <div class="form-group">
                  <label for="montoEfectivo">Monto:</label>
                  <span class="font-weight-bold">{{ (montoEfectivo = calcularTotal.toFixed(2)) }}</span>
                </div>
                <Button label="Generar QR" icon="pi pi-qrcode" class="p-button-primary p-mb-2" @click="generarQr" />
                <div v-if="qrImage" class="p-mb-2 p-text-center">
                  <img :src="qrImage" alt="Código QR" class="p-mx-auto" style="max-width: 100%; height: auto;" />
                </div>
                <Button label="Verificar Estado de Pago" icon="pi pi-check-circle" class="p-button-secondary p-mb-2" @click="verificarEstado" v-if="qrImage" />
                <div v-if="estadoTransaccion" class="p-card p-2">
                  <div class="font-weight-bold">Estado Actual:</div>
                  <div>
                    <span :class="'p-tag p-tag-' + badgeSeverity">{{ estadoTransaccion.objeto.estadoActual }}</span>
                  </div>
                </div>
              </template>
            </Card>
            <Button @click="registrarVenta(7)" label="Registrar Pago" icon="pi pi-check" class="p-button-success p-mt-2 p-button-lg p-button-raised" />
          </div>
        </div>
      </TabPanel>
    </TabView>
  </div>
</template>

                        <div v-if="tipoVenta === 'credito'">
                            <div class="p-grid">
                                <div class="p-col-4">
                                    <label for="numeroCuotas" class="font-weight-bold">Cantidad de cuotas <span
                                            class="p-error">*</span></label>
                                    <InputNumber id="numeroCuotas" v-model="numero_cuotas" :useGrouping="false" />
                                </div>

                                <div class="p-col-4">
                                    <label for="tiempoDias" class="font-weight-bold">Frecuencia de Pagos <span
                                            class="p-error">*</span></label>
                                    <div class="p-inputgroup">
                                        <InputNumber id="tiempoDias" v-model="tiempo_diaz" :useGrouping="false" />
                                        <span class="p-inputgroup-addon">Dias</span>
                                    </div>
                                </div>

                                <div class="p-col-4">
                                    <div class="p-field">
                                        <label class="font-weight-bold">Total</label>
                                        <div>
                                            {{
                                                (calcularTotal * parseFloat(monedaVenta[0])).toFixed(2)
                                            }}
                                            {{ monedaVenta[1] }}
                                        </div>
                                        <Button label="GENERAR CUOTAS" @click="generarCuotas"
                                            class="p-button-success" />
                                    </div>
                                </div>
                            </div>

                            <div class="p-field-checkbox">
                                <Checkbox v-model="primera_cuota" :binary="true" id="primera_cuota" />
                                <label for="primera_cuota">Primera cuota pagada</label>
                            </div>

                            <div v-if="primera_cuota" class="p-grid">
                                <div class="p-col-6">
                                    <label class="font-weight-bold">Monto a pagar</label>
                                    <InputNumber v-model="primer_precio_cuota" :useGrouping="false" />
                                </div>
                                <div class="p-col-6">
                                    <label class="font-weight-bold" for="tipo_pago">Tipo de Pago</label>
                                    <Dropdown id="tipo_pago" v-model="tipo_pago" :options="tiposPagoOptions"
                                        optionLabel="label" optionValue="value"
                                        @change="seleccionarTipoPago($event.value)" />
                                </div>
                            </div>

                            <DataTable :value="cuotas" class="p-mt-4" :paginator="true" :rows="10"
                                responsiveLayout="scroll">
                                <Column field="index" header="#">
                                    <template #body="slotProps">
                                        {{ slotProps.index + 1 }}
                                    </template>
                                </Column>
                                <Column field="fecha_pago" header="Fecha Pago">
                                    <template #body="slotProps">
                                        {{
                                            new Date(slotProps.data.fecha_pago).toLocaleDateString(
                                                "es-ES"
                                            )
                                        }}
                                    </template>
                                </Column>
                                <Column field="precio_cuota" header="Precio Cuota">
                                    <template #body="slotProps">
                                        {{
                                            (
                                                slotProps.data.precio_cuota * parseFloat(monedaVenta[0])
                                            ).toFixed(2)
                                        }}
                                        {{ monedaVenta[1] }}
                                    </template>
                                </Column>
                                <Column field="totalCancelado" header="Total Cancelado">
                                    <template #body="slotProps">
                                        {{
                                            (
                                                slotProps.data.totalCancelado *
                                                parseFloat(monedaVenta[0])
                                            ).toFixed(2)
                                        }}
                                        {{ monedaVenta[1] }}
                                    </template>
                                </Column>
                                <Column field="saldo_restante" header="Saldo">
                                    <template #body="slotProps">
                                        {{
                                            (
                                                slotProps.data.saldo_restante *
                                                parseFloat(monedaVenta[0])
                                            ).toFixed(2)
                                        }}
                                        {{ monedaVenta[1] }}
                                    </template>
                                </Column>
                                <Column field="fecha_cancelado" header="Fecha Cancelado">
                                    <template #body="slotProps">
                                        {{
                                            slotProps.data.fecha_cancelado
                                                ? new Date(
                                                    slotProps.data.fecha_cancelado
                                                ).toLocaleDateString("es-ES")
                                                : "Sin fecha"
                                        }}
                                    </template>
                                </Column>
                                <Column field="estado" header="Estado" />
                            </DataTable>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="cerrarModal3()">
                                    Volver
                                </button>
                                <button type="button" class="btn btn-primary" @click="registrarVenta()">
                                    Registrar
                                </button>
                            </div>
                        </div>
                    </div>

                    <template>
                        <footer class="footer d-flex justify-content-center gap-2">
                            <Button @click="prevStep" :disabled="step === 1" class="p-button-primary"
                                icon="pi pi-chevron-left" iconPos="left" label="Anterior" :style="{ width: '150px' }" />
                            <Button @click="validarYAvanzar" :disabled="step === 3" class="p-button-primary"
                                icon="pi pi-chevron-right" iconPos="right" label="Siguiente"
                                :style="{ width: '150px' }" />
                        </footer>
                    </template>

                </div>
            </Dialog>
        </template>

        <template>
            <Dialog :visible.sync="modal" :containerStyle="{ width: '100%', maxWidth: '800px', paddingTop: '35px' }"
                modal closable>
                <template #header>
                    <h3>{{ tituloModal }}</h3>
                </template>

                <TabView>
                    <TabPanel header="Articulos">
                        <div class="grid">
                            <div class="col-12 md:col-6 lg:col-6">
                                <span class="p-input-icon-left w-full">
                                    <i class="pi pi-search" />
                                    <InputText v-model="buscarA" placeholder="Buscar por código o nombre"
                                        @input="listarArticulo" class="w-full" />
                                </span>
                            </div>
                        </div>
                        <DataTable :value="arrayArticulo" :paginator="true" :rows="10" class="p-datatable-sm moto-table"
                            @row-select="onRowSelect" selectionMode="single" responsiveLayout="scroll"
                            :selection="selectedArticulo" breakpoint="960px">
                            <Column field="codigo" header="Código" :class="'sm:table-cell'" />
                            <Column field="nombre" header="Nombre" :class="'sm:table-cell'" />
                            <Column field="nombre_categoria" header="Categoría" :class="'hidden sm:table-cell'" />
                            <Column header="Precio Venta" :class="'hidden sm:table-cell'">
                                <template #body="slotProps">
                                    {{ (slotProps.data.precio_venta * parseFloat(monedaVenta[0])).toFixed(2) }}
                                    {{ monedaVenta[1] }}
                                </template>
                            </Column>
                            <Column field="saldo_stock" header="Stock" :class="'sm:table-cell'" />
                            <Column header="Estado" :class="'hidden sm:table-cell'">
                                <template #body="slotProps">
                                    <Tag :severity="slotProps.data.condicion ? 'success' : 'danger'"
                                        :value="slotProps.data.condicion ? 'Activo' : 'Desactivado'" />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>
                </TabView>
            </Dialog>
        </template>

    </main>
</template>

<script>
import Button from "primevue/button";
import Card from "primevue/card";
import Checkbox from "primevue/checkbox";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Dialog from "primevue/dialog";
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Message from "primevue/message";
import Paginator from "primevue/paginator";
import Panel from "primevue/panel";
import SelectButton from "primevue/selectbutton";
import Steps from "primevue/steps";
import Tag from "primevue/tag";
import Swal from "sweetalert2";
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Toast from 'primevue/toast';

export default {


    components: {
        Dropdown,
        DataTable,
        Column,
        Button,
        Paginator,
        Card,
        InputText,
        Button,
        Panel,
        Steps,
        Message,
        Dialog,
        Tag,
        SelectButton,
        InputNumber,
        Checkbox,
        TabView,
        TabPanel,
        Toast,
    },
    data() {
        return {
            tipoVentaSeleccionado: false,
            precioSeleccionado: null,
            searchTerm: '',
            selectedArticulo: null,
            debounceTimeout: null,
            tiposPagoOptions: [
                { label: "Efectivo", value: "efectivo" },
                { label: "Tarjeta", value: "tarjeta" },
                { label: "Transferencia", value: "transferencia" },
            ],
            detalles: [],
            opcionesPago: [
                { label: "Efectivo", value: "efectivo" },
                { label: "QR", value: "qr" },
            ],
            criterioOptions: [
                { label: "Nombre", value: "nombre" },
                { label: "Descripción", value: "descripcion" },
                { label: "Código", value: "codigo" },
            ],
            isDialogVisible: false,
            tipoComprobanteOptions: [{ name: "RECIBO", code: "RESIVO" }],
            opcionPago: "",
            tipoVenta: "",
            mostrarSpinner: false,
            selectedAlmacen: null,
            idrol: null,
            step: 1,
            modal2: false,
            modal: false,
            zIndexBase: 1050,
            //qr
            alias: "",
            montoQR: 0,
            qrImage: "",
            aliasverificacion: "",
            estadoTransaccion: null,
            currency: "BOB", // Define tu moneda
            resivo: "",
            clienteDeudas: 0,
            arrayCuotas: [],
            arraySeleccionado: null,
            cuotaSeleccionada: null,
            modalCuotas: 0,

            tipo_pago: "",
            criterioKit: "nombre",
            buscarKit: "",

            mensajesKit: [],
            arrayArticulosKit: [],
            datosFormularioKit: [],
            modalDetalleKit: 0,
            arrayKit: [],

            arrayPreciosEspeciales: [],
            modalDetalle: 0,
            datosFormularioPE: [],
            arrayArticulosPE: [],

            arrayPromocion: [],
            unidadPaquete: '1',  // Por defecto, establecido a '1' para venta por unidad
            tipoVentaOptions: [
                { label: "Por unidad", value: '1' },
                { label: "Por paquete", value: 'paquete' }
            ],

            monedaVenta: [],
            permitirDevolucion: "",
            saldosNegativos: 1,
            venta_id: 0,
            idcliente: 0,
            usuarioAutenticado: null,
            puntoVentaAutenticado: null,
            idsucursalAutenticado: null,
            cliente: "",
            email: "",
            nombreCliente: "",
            nombreClienteEditable: false,
            documento: "",
            tipo_documento: "1",
            complemento_id: "",
            descuentoAdicional: 0.0,
            descuentoGiftCard: "",
            tipo_comprobante: "RESIVO",
            serie_comprobante: "",
            last_comprobante: 0,
            num_comprob: "",
            impuesto: 0.18,
            total: 0.0,
            totalImpuesto: 0.0,
            totalParcial: 0.0,
            arrayVenta: [],
            arrayCliente: [],
            arrayDetalle: [],
            arrayProductos: [],
            listado: 1,
            tituloModal: "",
            tipoAccion: 0,
            errorVenta: 0,
            errorMostrarMsjVenta: [],
            pagination: {
                total: 0,
                current_page: 1,
                last_page: 0, // Asegúrate de actualizar este valor al obtener datos
            },
            offset: 3,
            criterio: "",
            buscar: "",
            criterioA: "",
            buscarA: "",
            arrayArticulo: [],

            idarticulo: 0,
            codigo: "",
            articulo: "",
            medida: "",
            codigoClasificador: "",
            codigoProductoSin: "",
            precio: 0,
            unidad_envase: 0,
            cantidad: 1,
            paquni: "",
            precioBloqueado: false,
            descuento: 0,
            descuentoProducto: 0,
            sTotal: 0,
            stock: 0,
            valorMaximoDescuento: "",
            mostrarDireccion: true,

            casosEspeciales: false,
            mostrarCampoCorreo: false,
            leyendaAl: "",
            codigoExcepcion: 0,
            mostrarSpinner: false,
            primer_precio_cuota: 0,
            numeroTarjeta: null,
            metodoPago: "",
            criterioVenta: "ci",
            //almacenes
            arrayAlmacenes: [],
            almacenSeleccionado: null,
            almacenPredeterminadoId: null,
            idAlmacen: null,
            //-----PRECIOS- AUMENTE 3/OCTUBRE--------
            precioseleccionado: "",
            //precio : '',
            arrayPrecios: [],
            nombre_precio: "",
            precio_uno: "",
            precio_dos: "",
            precio_tres: "",
            precio_cuatro: "",
            //-----MODAL 2---

            tituloModal2: "",
            tipoAccion2: "",

            modal3: 0,
            tituloModal3: "",
            tipoAccion3: "",

            recibido: 0,
            efectivo: 0,
            cambio: 0,
            faltante: 0,
            cantidadClientes: 0,
            idtipo_pago: "",
            idtipo_venta: 1,
            tiempo_diaz: "",
            numero_cuotas: "",
            cuotas: [], //---para almacenar las fechas
            estadocrevent: "activo",
            primera_cuota: "",
            habilitarPrimeraCuota: false,
            tipoPago: "EFECTIVO",
            tiposPago: {
                EFECTIVO: 1,
                TARJETA: 2,
                QR: 3,
            },
        };
    },

    watch: {
        codigo(newValue) {
            if (newValue) {
                this.buscarArticulo();
            }
        },
        documento(newDocumento) {
            this.mostrarCampoCorreo =
                newDocumento === "99002" || newDocumento === "99003";
        },
        arraySeleccionado: {
            handler() {
                this.fetchDetalles();
            },
            deep: true,
        },
    },
    computed: {
        calcularStockDisponible() {
            if (!this.arraySeleccionado) return 0;

            const stockTotal = parseInt(this.arraySeleccionado.saldo_stock);
            const unidadEnvase = parseInt(this.arraySeleccionado.unidad_envase);

            if (this.unidadPaquete === 'paquete') {
                return Math.floor(stockTotal / unidadEnvase);
            } else {
                return stockTotal;
            }
        },
        stockMostrado() {
            const stockDisponible = this.calcularStockDisponible;
            const cantidadSolicitada = this.unidadPaquete === 'paquete'
                ? this.cantidad
                : this.cantidad / this.arraySeleccionado.unidad_envase;
            return Math.floor(Math.max(0, stockDisponible - cantidadSolicitada));
        },


        resultadoMultiplicacion() {
            if (this.arraySeleccionado) {
                return this.precioseleccionado * this.unidadPaquete * this.cantidad;
            }
        },

        totalCantidades() {
            return this.arrayArticulosKit.reduce((total, articulo) => {
                return total + parseInt(articulo.cantidad);
            }, 0);
        },

        isActived: function () {
            return this.pagination.current_page;
        },

        //Calcula los elementos de la paginación
        pagesNumber() {
            let from = this.pagination.current_page - 2;
            if (from < 1) {
                from = 1;
            }
            let to = from + 4;
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            let pagesArray = [];
            for (let page = from; page <= to; page++) {
                pagesArray.push(page);
            }
            return pagesArray;
        },

        calcularTotal() {
            var resultado = 0.0;
            for (var i = 0; i < this.arrayDetalle.length; i++) {
                resultado +=
                    this.arrayDetalle[i].precioseleccionado *
                    this.arrayDetalle[i].cantidad -
                    (this.arrayDetalle[i].precioseleccionado *
                        this.arrayDetalle[i].cantidad *
                        this.arrayDetalle[i].descuento) /
                    100;
            }
            resultado -= this.descuentoAdicional;
            //resultado -= this.descuentoGiftCard;
            return resultado;
        },

        badgeSeverity() {
            if (
                this.estadoTransaccion &&
                this.estadoTransaccion.objeto.estadoActual === "PENDIENTE"
            ) {
                return "danger"; // Rojo para estado PENDIENTE
            } else if (
                this.estadoTransaccion &&
                this.estadoTransaccion.objeto.estadoActual === "PAGADO"
            ) {
                return "success"; // Verde para estado PAGADO
            } else {
                return "info"; // Otros estados
            }
        },
    },

    methods: {
        seleccionarTipoVenta(tipo) {
            this.tipoVenta = tipo;
            this.tipoVentaSeleccionado = true;
        },
        actualizarVistaStock() {
            // Forzar la actualización de la vista
            this.$forceUpdate();
        },
        encuentra(id) {
            return this.arrayDetalle.some(item => item.idarticulo === id);
        },
        seleccionarPrecio(precio) {
            this.precioSeleccionado = precio;
        },
        formatearPrecio(precio) {
            // Asegúrate de que esta función esté definida y funcione correctamente
            return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(precio);
        },
        onRowSelect(event) {
            this.agregarDetalleModal(event.data);
        },
        debouncedSearch() {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                this.performSearch();
            }, 300); // Espera 300ms después de que el usuario deje de escribir
        },
        performSearch() {
            if (this.searchTerm.length < 2) {
                this.arrayArticulo = [];
                return;
            }

            const url = `/articulo/listarArticuloVenta?buscar=${this.searchTerm}&idAlmacen=${this.idAlmacen}`;

            axios.get(url)
                .then(response => {
                    this.arrayArticulo = response.data.articulos;
            
                })
                .catch(error => {
                    console.error("Error en la búsqueda:", error);
                    this.arrayArticulo = [];
                });
        },

        decrementarCantidad() {
            this.cantidad = Math.max(1, this.cantidad - 1);
        },
        incrementarCantidad() {
            this.cantidad++;
        },
        formatearPrecio(precio) {
            // Verificar si precio es null o undefined
            if (precio == null) {
                return "N/A";
            }

            // Convertir precio a número si es una cadena
            let precioNumerico =
                typeof precio === "string" ? parseFloat(precio) : precio;

            // Verificar si es un número válido
            if (isNaN(precioNumerico)) {
                return "N/A";
            }

            // Formatear el precio
            return `${precioNumerico.toFixed(2)} ${this.monedaVenta[1]}`;
        },
        fetchDetalles() {
            if (this.arraySeleccionado && this.arraySeleccionado.id) {
                axios
                    .get("/venta/obtenerDetalles", {
                        params: {
                            id: this.arraySeleccionado.id,
                        },
                    })
                    .then((response) => {
                        this.detalles = response.data.detalles;
                    })
                    .catch((error) => {
                        console.error("Error fetching details:", error);
                    });
            }
        },
        generarCuotas() {
            this.cuotas = [];
            const fechaHoy = new Date();

            const montoEntero = Math.floor(this.calcularTotal / this.numero_cuotas);
            const montoDecimal = (
                this.calcularTotal -
                montoEntero * (this.numero_cuotas - 1)
            ).toFixed(2);

            let fechaInicioPago;
            let saldoRestante;
            let estadoCuota;

            if (this.primera_cuota) {
                const primerPago = Number(this.primer_precio_cuota) || 0;
                fechaInicioPago = fechaHoy;
                saldoRestante = (this.calcularTotal - primerPago).toFixed(2);
                estadoCuota = "Pagado";

                const primeraCuota = {
                    fecha_pago: `${fechaHoy.getFullYear()}-${fechaHoy.getMonth() + 1}-${fechaHoy.getDate()} ${fechaHoy.toLocaleTimeString()}`,
                    precio_cuota: primerPago.toFixed(2),
                    totalCancelado: primerPago.toFixed(2),
                    saldo_restante: saldoRestante,
                    fecha_cancelado: `${fechaHoy.getFullYear()}-${fechaHoy.getMonth() + 1}-${fechaHoy.getDate()} ${fechaHoy.toLocaleTimeString()}`,
                    estado: "Pagado",
                };

                this.cuotas.push(primeraCuota);

                const montoRestante = this.calcularTotal - primerPago;
                const montoEnteroRestante = Math.floor(
                    montoRestante / (this.numero_cuotas - 1)
                );
                const montoDecimalRestante = (
                    montoRestante -
                    montoEnteroRestante * (this.numero_cuotas - 2)
                ).toFixed(2);

                saldoRestante = montoRestante;
                fechaInicioPago = new Date(
                    fechaHoy.getTime() + this.tiempo_diaz * 24 * 60 * 60 * 1000
                );
                estadoCuota = "Pendiente";

                for (let i = 1; i < this.numero_cuotas; i++) {
                    const fechaPago = new Date(
                        fechaInicioPago.getTime() +
                        (i - 1) * this.tiempo_diaz * 24 * 60 * 60 * 1000
                    );
                    const dia = fechaPago.getDate();
                    const mes = fechaPago.getMonth() + 1;
                    const año = fechaPago.getFullYear();

                    const cuota = {
                        fecha_pago: `${año}-${mes}-${dia} ${fechaPago.toLocaleTimeString()}`,
                        precio_cuota:
                            i === this.numero_cuotas - 1
                                ? parseFloat(montoDecimalRestante).toFixed(2)
                                : montoEnteroRestante,
                        totalCancelado: 0,
                        saldo_restante: saldoRestante,
                        fecha_cancelado: null,
                        estado: "Pendiente",
                    };

                    saldoRestante -= cuota.precio_cuota;
                    saldoRestante = saldoRestante.toFixed(2);

                    this.cuotas.push(cuota);
                }
            } else {
                fechaInicioPago = new Date(
                    fechaHoy.getTime() + this.tiempo_diaz * 24 * 60 * 60 * 1000
                );
                saldoRestante = this.calcularTotal;
                estadoCuota = "Pendiente";

                for (let i = 0; i < this.numero_cuotas; i++) {
                    const fechaPago = new Date(
                        fechaInicioPago.getTime() +
                        i * this.tiempo_diaz * 24 * 60 * 60 * 1000
                    );
                    const dia = fechaPago.getDate();
                    const mes = fechaPago.getMonth() + 1;
                    const año = fechaPago.getFullYear();

                    const cuota = {
                        fecha_pago: `${año}-${mes}-${dia} ${fechaPago.toLocaleTimeString()}`,
                        precio_cuota:
                            i === this.numero_cuotas - 1
                                ? parseFloat(montoDecimal).toFixed(2)
                                : montoEntero,
                        totalCancelado: 0,
                        saldo_restante: saldoRestante,
                        fecha_cancelado: null,
                        estado: "Pendiente",
                    };

                    saldoRestante -= cuota.precio_cuota;
                    saldoRestante = saldoRestante.toFixed(2);

                    this.cuotas.push(cuota);
                }
            }
        },
        seleccionarTipoVenta(tipo) {
            this.tipoVenta = tipo;
            this.idtipo_venta = tipo === "contado" ? 1 : 2;
            this.opcionPago = ""; // Reinicia la opción de pago al cambiar el tipo de venta
        },
        buscarVenta() {
            this.listarVenta(1, this.buscar);
        },

        validarYAvanzar() {
            const errores = [];

            if (this.step === 2) {
                if (!this.selectedAlmacen) {
                    errores.push("Seleccione un almacén");
                }

                if (this.arrayDetalle.length === 0) {
                    errores.push("Añada al menos un artículo a la tabla");
                }
            }

            if (errores.length > 0) {
                const mensaje = errores.join("\n");
                swal("Campos incompletos", mensaje, "warning");
            } else {
                this.nextStep();
            }
        },
        cerrarModal2() {
            this.modal2 = false;
        },
        nextStep() {
            if (this.step < 3) {
                this.step++;
            }
        },
        prevStep() {
            if (this.step > 1) {
                this.step--;
            }
        },

        actualizarFechaHora() {
            const now = new Date();
            this.alias = now.toLocaleString();
        },
        verificarEstado() {
            axios
                .post("/qr/verificarestado", {
                    alias: this.aliasverificacion,
                })
                .then((response) => {
                    this.estadoTransaccion = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        generarQr() {
            this.aliasverificacion = this.alias;
            axios
                .post("/qr/generarqr", {
                    alias: this.alias,
                    monto: this.calcularTotal,
                })
                .then((response) => {
                    const imagenBase64 = response.data.objeto.imagenQr;
                    this.qrImage = `data:image/png;base64,${imagenBase64}`;
                })
                .catch((error) => {
                    console.error(error);
                });

            this.alias = "";
            this.montoQR = 0;
        },

        calcularPrecioUnitario(articulo) {
            // Lógica para calcular el precio unitario según el rango total de cantidades
            if (
                this.totalCantidades >= this.datosFormularioPE.rango_inicio_r1 &&
                this.totalCantidades <= this.datosFormularioPE.rango_final_r1
            ) {
                return this.datosFormularioPE.precio_r1;
            } else if (
                this.totalCantidades >= this.datosFormularioPE.rango_inicio_r2 &&
                this.totalCantidades <= this.datosFormularioPE.rango_final_r2
            ) {
                return this.datosFormularioPE.precio_r2;
            } else if (
                this.totalCantidades >= this.datosFormularioPE.rango_inicio_r3 &&
                this.totalCantidades <= this.datosFormularioPE.rango_final_r3
            ) {
                return this.datosFormularioPE.precio_r3;
            } else {
                // Precio predeterminado si no está en ningún rango
                return articulo.precio_costo_unid;
            }
        },
        getClassByCantidad(total) {
            if (
                total >= this.datosFormularioPE.rango_inicio_r1 &&
                total <= this.datosFormularioPE.rango_final_r1
            ) {
                return "rango-1"; // clase para el rango 1
            } else if (
                total >= this.datosFormularioPE.rango_inicio_r2 &&
                total <= this.datosFormularioPE.rango_final_r2
            ) {
                return "rango-2"; // clase para el rango 2
            } else if (
                total >= this.datosFormularioPE.rango_inicio_r3 &&
                total <= this.datosFormularioPE.rango_final_r3
            ) {
                return "rango-3"; // clase para el rango 3
            } else {
                return ""; // clase por defecto si no se cumple ningún rango
            }
        },
        abrirTipoVenta() {
            if (this.idtipo_venta == 1) {
                this.modal2 = true;
                this.cliente = this.nombreCliente;
                this.tipoAccion2 = 1;
              
            }
        },

        seleccionarTipoPago(tipo) {
            console.log("TIPO PAGO ", tipo);
            this.tipoPago = tipo;
            this.tituloModal2 = `TIPO DE PAGO : ${tipo}`;
            this.idtipo_pago = this.tiposPago[tipo];
        },

        habilitarNombreCliente() {
            if (this.casosEspeciales) {
                this.$refs.nombreRef.removeAttribute("readonly");
                this.documento = "99001";
                this.idcliente = "2";
                this.tipo_documento = "5";
            } else {
                this.$refs.nombreRef.setAttribute("readonly", true);
                this.documento = "";
                this.idcliente = "";
                this.tipo_documento = "";
            }
        },

        async obtenerDatosUsuario() {
            try {
                const response = await axios.get("/venta");
                this.usuarioAutenticado = response.data.usuario.usuario;
                this.usuario_autenticado = this.usuarioAutenticado;
                this.idrol = response.data.usuario.idrol;
                this.idsucursalAutenticado = response.data.usuario.idsucursal;
                console.log("Obtener Datos Usuario: " + this.idsucursalAutenticado);
                this.puntoVentaAutenticado = response.data.codigoPuntoVenta;
            } catch (error) {
                console.error(error);
            }
        },

        async obtenerDatosSesionYComprobante() {
            try {
                const idsucursal = this.idsucursalAutenticado;
                console.log("El idsucursal es: " + idsucursal);
                const response = await axios.get("/obtener-ultimo-comprobante", {
                    params: {
                        idsucursal: idsucursal,
                    },
                });
                const lastComprobante = response.data.last_comprobante;
                this.last_comprobante = lastComprobante;
                console.log("El ultimo comprobante es: " + this.last_comprobante);
                this.nextNumber(lastComprobante);
            } catch (error) {
                console.error("Error al obtener el último comprobante:", error);
            }
        },

        async ejecutarFlujoCompleto() {
            await this.obtenerDatosUsuario();
            await this.obtenerDatosSesionYComprobante();
        },

        nextNumber() {
            if (!this.num_comprob || this.num_comprob === "") {
                this.last_comprobante++;
                // Completa con ceros a la izquierda hasta alcanzar 5 dígitos
                this.num_comprob = this.last_comprobante.toString().padStart(5, "0");
            }
        },

        listarVenta(page, buscar, criterio) {
            let me = this;
            var url =
                "/venta?page=" + page + "&buscar=" + buscar + "&criterio=" + criterio;
            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                   
                    me.arrayVenta = respuesta.ventas.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        selectCliente(numero) {
            let me = this;
            var url = "/cliente/selectClientePorNumero?numero=" + numero;
            axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    q: numero;
                    me.arrayCliente = respuesta.clientes;
             
                    me.cantidadClientes = me.arrayCliente.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        selectClienteNombre(nombre) {
            console.log("nombre ", nombre);
            let me = this;
            var url = "/cliente/selectCliente?filtro=" + nombre;
            axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    q: nombre;
                    me.arrayCliente = respuesta.clientes;
           
                    me.cantidadClientes = me.arrayCliente.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        getDatosCliente(val1) {
            let me = this;
            if (val1 !== null) {
                me.loading = true;
                me.idcliente = val1.id;
                //console.log(val1);
                this.email = val1.email;
                this.nombreCliente = val1.nombre;
                this.documento = val1.num_documento;
                this.tipo_documento = val1.tipo_documento;
                this.complemento_id = val1.complemento_id;
                this.clienteDeudas = val1.cantidad_creditos;
            } else {
                //console.log(val1);
                this.email = "";
                this.nombreCliente = "";
                this.documento = "";
                this.tipo_documento = "";
                this.complemento_id = "";
                this.clienteDeudas = "";
            }
        },
        getDatosCliente2(val1) {
            let me = this;
            me.loading = true;
            if (val1 !== null) {
                me.loading = true;
                me.idcliente = val1.id;
                //console.log(val1);
                this.email = val1.email;
                this.nombreCliente = val1.nombre;
                this.documento = val1.num_documento;
                this.tipo_documento = val1.tipo_documento;
                this.complemento_id = val1.complemento_id;
                this.clienteDeudas = val1.cantidad_creditos;
            } else {
                //console.log(val1);
                this.email = "";
                this.nombreCliente = "";
                this.documento = "";
                this.tipo_documento = "";
                this.complemento_id = "";
                this.clienteDeudas = "";
            }
        },
        buscarArticulo() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                let me = this;
                var url =
                    "/articulo/buscarArticuloVenta?filtro=" +
                    me.codigo +
                    "&idalmacen=" +
                    me.selectedAlmacen;
                axios
                    .get(url)
                    .then(function (response) {
                        let respuesta = response.data;
                        me.arraySeleccionado = respuesta.articulos[0];
                
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }, 1000);
        },
        pdfVenta(id) {
            window.open("/venta/pdf/" + id, "_blank");
        },
        onPageChange(event) {
            let page = event.page + 1; // PrimeVue pages are 0-based, while your logic uses 1-based
            this.cambiarPagina(page, this.buscar, this.criterio);
        },
        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            this.listarVenta(page, buscar, criterio);
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
        eliminarDetalle(id) {
            const index = this.arrayDetalle.findIndex((item) => item.id === id);
            if (index !== -1) {
                this.arrayDetalle.splice(index, 1);
                this.arrayProductos.splice(index, 1);
                this.calcularTotal();
            }
        },
        eliminarKit(id) {
            const indicesEliminar = [];
            for (let i = 0; i < this.arrayDetalle.length; i++) {
                if (this.arrayDetalle[i].idkit === id) {
                    indicesEliminar.push(i);
                }
            }
            indicesEliminar.forEach((index) => {
                this.arrayProductos.splice(index, 1);
            });
            for (let i = indicesEliminar.length - 1; i >= 0; i--) {
                this.arrayDetalle.splice(indicesEliminar[i], 1);
            }
        },

        agregarDetalle() {
            if (this.encuentra(this.arraySeleccionado.id)) {
                swal({
                    type: "error",
                    title: "Error...",
                    text: "Este Artículo ya se encuentra agregado!",
                });
                return;
            }

            const stockDisponible = this.calcularStockDisponible;
            const cantidadSolicitada = this.unidadPaquete === 'paquete' ? this.cantidad : this.cantidad / this.arraySeleccionado.unidad_envase;

            if (this.saldosNegativos === 0 && stockDisponible < cantidadSolicitada) {
                swal({
                    type: "error",
                    title: "Error...",
                    text: "No hay stock disponible!",
                });
                return;
            }

            if (!this.precioSeleccionado) {
                swal({
                    type: "error",
                    title: "Error...",
                    text: "Por favor, seleccione un precio antes de agregar",
                });
                return;
            }

            const precioUnitario = parseFloat(this.precioSeleccionado);
            const cantidadTotal = this.unidadPaquete === 'paquete'
                ? this.cantidad * this.arraySeleccionado.unidad_envase
                : this.cantidad;
            const descuento = (
                precioUnitario *
                cantidadTotal *
                (this.descuentoProducto / 100)
            ).toFixed(2);
            const total = (precioUnitario * cantidadTotal - descuento).toFixed(2);

            const nuevoDetalle = {
                id: Date.now(),
                idkit: -1,
                idarticulo: this.arraySeleccionado.id,
                articulo: this.arraySeleccionado.nombre,
                medida: this.arraySeleccionado.medida,
                unidad_envase: this.arraySeleccionado.unidad_envase,
                cantidad: cantidadTotal,
                cantidad_paquetes: this.unidadPaquete === 'paquete' ? this.cantidad : cantidadTotal / this.arraySeleccionado.unidad_envase,
                precio: precioUnitario,
                descuento: this.descuentoProducto,
                stock: this.arraySeleccionado.saldo_stock,
                precioseleccionado: precioUnitario,
                total: total,
            };

            this.arrayDetalle.push(nuevoDetalle);

            const nuevoProducto = {
                actividadEconomica: 461021,
                codigoProductoSin: this.arraySeleccionado.codigoProductoSin,
                codigoProducto: this.arraySeleccionado.codigo,
                descripcion: this.arraySeleccionado.nombre,
                cantidad: cantidadTotal,
                unidadMedida: this.arraySeleccionado.codigoClasificador,
                precioUnitario: precioUnitario.toFixed(2),
                montoDescuento: descuento,
                subTotal: total,
                numeroSerie: null,
                numeroImei: null,
            };

            this.arrayProductos.push(nuevoProducto);

            this.precioBloqueado = true;
            this.arraySeleccionado = [];
            this.cantidad = 1;
            this.unidadPaquete = '1';  // Reseteamos a 'Por unidad'
            this.codigo = "";
            this.descuentoProducto = 0;
            this.precioSeleccionado = null;  // Reseteamos el precio seleccionado

            this.calcularTotal();
            this.actualizarVistaStock();
        },

        agregarDetalleModal(data) {
            this.codigo = data.codigo;
            this.precioSeleccionado = data.precio_uno;  // Cambiamos precioseleccionado a precioSeleccionado
            this.unidadPaquete = '1';
            this.cerrarModal();
        },
        eliminarSeleccionado() {
            this.codigo = "";
            this.arraySeleccionado = [];
        },
        listarArticulo() {
            let url = "/articulo/listarArticuloVenta?buscar=" + this.buscarA + "&idAlmacen=" + this.idAlmacen;

            axios.get(url)
                .then(response => {
                    this.arrayArticulo = response.data.articulos;
                
                })
                .catch(error => {
                    console.error("Error en la búsqueda:", error);
                });
        },
        datosConfiguracion() {
            let me = this;
            var url = "/configuracion";

            axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    console.log(respuesta);
                    me.saldosNegativos = respuesta.configuracionTrabajo.saldosNegativos;
                    me.permitirDevolucion =
                        respuesta.configuracionTrabajo.permitirDevolucion;
                    me.monedaVenta = [
                        respuesta.configuracionTrabajo.valor_moneda_venta,
                        respuesta.configuracionTrabajo.simbolo_moneda_venta,
                    ];
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        async selectAlmacen() {
            let me = this;
            let url = "/almacen/selectAlmacen";
            await axios
                .get(url)
                .then(function (response) {
                    let respuesta = response.data;
                    me.arrayAlmacenes = respuesta.almacenes;
                    console.log(me.arrayAlmacenes);
                    me.obtenerAlmacenPredeterminado();
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        async obtenerAlmacenPredeterminado() {
            try {
                const response = await axios.get(
                    "/api/configuracion/almacen-predeterminado"
                );
                this.almacenPredeterminadoId = response.data.almacen_predeterminado_id;
                console.log(
                    "El almacen predeterminado es : " + this.almacenPredeterminadoId
                );

                this.almacenSeleccionado = this.arrayAlmacenes.find(
                    (almacen) => almacen.id === this.almacenPredeterminadoId
                );
            } catch (error) {
                console.error("Error al obtener el almacén predeterminado:", error);
            }
        },

        getAlmacenProductos(event) {
            this.idAlmacen = event.value;
        },

        aplicarDescuento() {
            const descuentoGiftCard = this.descuentoGiftCard;
            const numeroTarjeta = this.numeroTarjeta;
            let idtipo_pago;

            if (numeroTarjeta && descuentoGiftCard) {
                idtipo_pago = 86;
            } else if (numeroTarjeta && !descuentoGiftCard) {
                idtipo_pago = 10;
            } else {
                idtipo_pago = descuentoGiftCard ? 35 : 1;
            }

            this.registrarVenta(idtipo_pago);
        },

     

        otroMetodo(metodoPago) {
            const idtipo_pago = metodoPago;
            this.registrarVenta(idtipo_pago);
        },
        emitirComprobante() {
            if (!this.tipo_comprobante) {
                alert("Por favor seleccione un tipo de comprobante.");
                return;
            }

            if (this.tipo_comprobante === "RESIVO") {
                this.emitirResivo();
            } else if (this.tipo_comprobante === "FACTURA") {
                this.emitirFactura();
            }
        },
        async emitirResivo(idVentaRecienRegistrada) {
            let me = this;

            let idventa = idVentaRecienRegistrada;
            let numeroResivo = document.getElementById("num_comprobante").value;
            let id_cliente = document.getElementById("idcliente").value;
            let nombreRazonSocial = document.getElementById("nombreCliente").value;
            let numeroDocumento = document.getElementById("documento").value;
            let complemento = document.getElementById("complemento_id").value;
            let tipoDocumentoIdentidad =
                document.getElementById("tipo_documento").value;
            let montoTotal = (
                this.calcularTotal * parseFloat(this.monedaVenta[0])
            ).toFixed(2);
            let usuario = document.getElementById("usuarioAutenticado").value;

            try {
                const response = await axios.get("/resivo/obtenerLeyendaAleatoria");
                this.leyendaAl = response.data.descripcionLeyenda;
                console.log("El dato de leyenda llegado es: " + this.leyendaAl);
            } catch (error) {
                console.error(error);
                return '"Ley N° 453: Los servicios deben suministrarse en condiciones de inocuidad, calidad y seguridad."';
            }

            var resivo = [];
            resivo.push({
                cabecera: {
                    municipio: "Cochabamba",        
                    telefono: "77490451",
                    numeroResivo: numeroResivo,
                    codigoSucursal: 0,
                    direccion: "Av. Ejemplo 123",
                    codigoPuntoVenta: 0,
                    fechaEmision: new Date().toISOString().slice(0, -1),
                    nombreRazonSocial: nombreRazonSocial,
                    codigoTipoDocumentoIdentidad: tipoDocumentoIdentidad,
                    numeroDocumento: numeroDocumento,
                    complemento: complemento,
                    codigoCliente: numeroDocumento,
                    montoTotal: montoTotal,
                    codigoMoneda: 1,
                    tipoCambio: 1,
                    montoTotalMoneda: montoTotal,
                    usuario: usuario,
                    leyenda: this.leyendaAl,
                },
            });
            me.arrayProductos.forEach(function (prod) {
                resivo.push({ detalle: prod });
            });

            var datos = { resivo };

            axios
                .post("/venta/emitirResivo", {
                    resivo: datos,
                    id_cliente: id_cliente,
                    idventa: idventa,
                })
                .then(function (response) {
                    var data = response.data;

                    if (data === "VALIDADA") {
                        swal("RESIVO VALIDADO", "Correctamente", "success");
                        me.arrayProductos = [];
                        me.cerrarModal2();
                        me.cerrarModal3();
                        me.listarVenta(1, "", "id");
                        me.mostrarSpinner = false;
                    } else {
                        me.arrayProductos = [];
                        me.cerrarModal2();
                        me.cerrarModal3();
                        me.listarVenta(1, "", "id");
                        me.mostrarSpinner = false;
                        swal("RESIVO VALIDADO", "éxito", "success");
                    }
                })
                .catch(function (error) {
                    me.arrayProductos = [];
                    swal("INTENTE DE NUEVO", "Comunicacion fallida", "error");
                    me.mostrarSpinner = false;
                });
        },

        imprimirResivo(id) {
            swal({
                title: "Selecciona un tamaño para imprimir el recibo",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "CARTA",
                cancelButtonText: "ROLLO",
                reverseButtons: true,
            })
                .then((result) => {
                    if (result.value) {
                        console.log("Se seleccionó imprimir en CARTA");
                        axios
                            .get("/resivo/imprimirCarta/" + id, {
                                responseType: "blob",
                            })
                            .then(function (response) {
                                const url = window.URL.createObjectURL(
                                    new Blob([response.data])
                                );
                                const link = document.createElement("a");
                                link.href = url;
                                link.setAttribute("download", "recibo_carta.pdf");
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                                console.log("Se imprimió el recibo en CARTA correctamente");
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    } else if (result.dismiss === swal.DismissReason.cancel) {
                        console.log("Se seleccionó imprimir en ROLLO");
                        axios
                            .get("/resivo/imprimirRollo/" + id, {
                                responseType: "blob",
                            })
                            .then(function (response) {
                                const url = window.URL.createObjectURL(
                                    new Blob([response.data])
                                );
                                const link = document.createElement("a");
                                link.href = url;
                                link.setAttribute("download", "recibo_rollo.pdf");
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                                console.log("Se imprimió el recibo en ROLLO correctamente");
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    }
                    s;
                })
                .catch((error) => {
                    console.error("Error al mostrar el diálogo:", error);
                });
        },

        async buscarOCrearCliente() {
            try {
                // Primero, intenta buscar el cliente
                const response = await axios.get(
                    `/api/clientes/existe?documento=${this.documento}`
                );

                if (response.data.existe) {
                    // Si el cliente existe, usa ese ID y actualiza los datos del cliente
                    this.idcliente = response.data.cliente.id;
                    this.nombreCliente = response.data.cliente.nombre;
                    this.tipo_documento = response.data.cliente.tipo_documento;
                    this.complemento_id = response.data.cliente.complemento_id;
                } else {
                    // Si el cliente no existe, intenta crearlo
                    const nuevoClienteResponse = await axios.post("/cliente/registrar", {
                        nombre: this.nombreCliente,
                        num_documento: this.documento,
                        tipo_documento: "5",
                    });
                    this.idcliente = nuevoClienteResponse.data.id;
                }
            } catch (error) {
                console.error("Error al buscar o crear cliente:", error);
                // Manejar el error adecuadamente
            }
        },

        async registrarVenta(idtipo_pago) {
            if (this.validarVenta()) {
                this.prepararDatosCliente();
                await this.buscarOCrearCliente();

                const ventaData = this.prepararDatosVenta(idtipo_pago);

                try {
                    this.mostrarSpinner = true;
                    const response = await axios.post("/venta/registrar", ventaData);

                    if (response.data.id > 0) {
                        this.manejarVentaExitosa(response.data.id);
                    } else {
                        this.manejarErrorVenta(response.data);
                    }
                } catch (error) {
                    console.error("Error al registrar venta:", error);
                    this.ejecutarFlujoCompleto();
                } finally {
                    this.mostrarSpinner = false;
                }
            }
        },

        cambiarProducto(index, nuevoProducto) {
            if (index >= 0 && index < this.arrayDetalle.length) {
                this.arrayDetalle[index] = {
                    idarticulo: nuevoProducto.id,
                    articulo: nuevoProducto.nombre,
                    cantidad: 1,
                    precio: nuevoProducto.precio,
                    stock: nuevoProducto.stock,
                    subtotal: nuevoProducto.precio,
                };
                this.calcularTotal();
            }
        },

        prepararDatosCliente() {
            if (!this.nombreCliente.trim()) {
                this.nombreCliente = "SIN NOMBRE";
                this.documento = "000000";
            }
        },

        prepararDatosVenta(idtipo_pago) {
            const datosComunes = {
                idcliente: this.idcliente,
                tipo_comprobante: this.tipo_comprobante,
                serie_comprobante: this.serie_comprobante,
                num_comprobante: this.num_comprob,
                impuesto: this.impuesto,
                total: this.calcularTotal,
                idAlmacen: this.idAlmacen,
                idtipo_pago,
                idtipo_venta: this.idtipo_venta,
                data: this.arrayDetalle,
            };

            if (this.tipoVenta === "credito") {
                const totalCredito = this.primera_cuota
                    ? this.calcularTotal - this.primer_precio_cuota
                    : this.calcularTotal;

                let cuotasActualizadas = [...this.cuotas];
                if (this.primera_cuota) {
                    cuotasActualizadas[0] = {
                        ...cuotasActualizadas[0],
                        totalCancelado: this.primer_precio_cuota,
                        estado: "Pagado",
                    };
                }
                return {
                    ...datosComunes,
                    idpersona: this.idcliente,
                    numero_cuotasCredito: this.numero_cuotas,
                    tiempo_dias_cuotaCredito: this.tiempo_diaz,
                    totalCredito: this.primera_cuota
                        ? this.calcularTotal - this.cuotas[0].totalCancelado
                        : this.calcularTotal,
                    estadoCredito: "Pendiente",
                    cuotaspago: cuotasActualizadas,
                    primer_precio_cuota: this.primer_precio_cuota,
                    primera_cuota_pagada: this.primera_cuota,
                };
            } else if (this.tipo_comprobante === "RESIVO") {
                return { ...datosComunes, resivo: this.resivo };
            } else {
                return { ...datosComunes, idpersona: this.idcliente };
            }
        },

        manejarVentaExitosa(idVenta) {
            this.listado = 1;
            this.ejecutarFlujoCompleto();
            this.listarVenta(1, "", "num_comprob");
            this.cerrarModal2();
            this.cerrarModal3();

            if (this.tipoVenta === "credito") {
                swal(
                    "Venta Exitosa",
                    "La venta a crédito se ha registrado correctamente.",
                    "success"
                );
            } else {
                this.imprimirResivo(idVenta);
            }

            this.reiniciarFormulario();
        },

        manejarErrorVenta(data) {
            if (data.valorMaximo) {
                swal(
                    "Aviso",
                    `El valor de descuento no puede exceder el ${data.valorMaximo}`,
                    "warning"
                );
            } else if (data.caja_validado) {
                swal("Aviso", data.caja_validado, "warning");
            } else {
                console.error("Error desconocido al registrar venta:", data);
            }
        },

        reiniciarFormulario() {
            // Restablecer todos los valores del formulario
            Object.assign(this, {
                idproveedor: 0,
                tipo_comprobante: "RESIVO",
                nombreCliente: "",
                idcliente: 0,
                tipo_documento: 0,
                complemento_id: "",
                documento: "",
                imagen: "",
                serie_comprobante: "",
                num_comprob: "",
                impuesto: 0.18,
                total: 0.0,
                idarticulo: 0,
                articulo: "",
                cantidad: 1,
                precio: 0,
                stock: 0,
                codigo: "",
                descuento: 0,
                arrayDetalle: [],
                primer_precio_cuota: 0,
                step: 1,
                recibido: 0,
                tipoVenta: "",
                tipoPago: "",
                numero_cuotas: 0,
                tiempo_diaz: 0,
                primera_cuota: false,
                cuotas: [],
                selectedArticulo: null,  // Restablecer el artículo seleccionado
                arraySeleccionado: [],
            });
        },
        validarVenta() {
            let me = this;
            me.errorVenta = 0;
            me.errorMostrarMsjVenta = [];

            // Verificar stock de cada artículo
            me.arrayDetalle.forEach(function (x) {
                if (x.cantidad > x.stock) {
                    let art = `${x.articulo}: Stock insuficiente`;
                    me.errorMostrarMsjVenta.push(art);
                }
            });

           
            // Verificar si hay errores
            if (me.errorMostrarMsjVenta.length) {
                me.errorVenta = 1;

                // Mostrar todos los errores en un solo mensaje de SweetAlert
                swal({
                    type: "error",
                    title: "Error en la venta",
                    text: me.errorMostrarMsjVenta.join("\n"),
                });
            }

            return true;
        },

        verVenta(id) {
            let me = this;
            me.listado = 2;

            //Obtener datos del ingreso
            var arrayVentaT = [];
            var url = "/venta/obtenerCabecera?id=" + id;

            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    arrayVentaT = respuesta.venta;
                    console.log("VIENDO ", respuesta);

                    me.cliente = arrayVentaT[0]["nombre"];
                    me.tipo_comprobante = arrayVentaT[0]["tipo_comprobante"];
                    me.serie_comprobante = arrayVentaT[0]["serie_comprobante"];
                    me.num_comprobante = arrayVentaT[0]["num_comprobante"];
                    me.impuesto = arrayVentaT[0]["impuesto"];
                    me.total = arrayVentaT[0]["total"];
                })
                .catch(function (error) {
                    console.log(error);
                });

            //obtener datos de los detalles
            var url = "/venta/obtenerDetalles?id=" + id;

            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayDetalle = respuesta.detalles;
                    console.log(array);
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        cerrarModal() {
            this.modal = false;
      
        },
        abrirModal() {
            this.listarArticulo("", "nombre");
            this.selectAlmacen();
            this.arrayArticulo = [];
            this.modal = true;
            this.tituloModal = "Seleccione los articulos que desee";
           
        },

        advertenciaFechaVencimiento() {
            swal({
                title: "No Disponible",
                text: "No puede seleccionar este producto porque esta vencido.",
                type: "warning",
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Aceptar",
                confirmButtonClass: "btn btn-success",
                buttonsStyling: false,
            }).then(() => {
                this.cerrarModal();
            });
        },

        desactivarVenta(id) {
            swal({
                title: "Esta seguro de anular esta venta?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Aceptar!",
                cancelButtonText: "Cancelar",
                confirmButtonClass: "btn btn-success",
                cancelButtonClass: "btn btn-danger",
                buttonsStyling: false,
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {
                    let me = this;

                    axios
                        .put("/venta/desactivar", {
                            id: id,
                        })
                        .then(function (response) {
                            me.listarVenta(1, "", "num_comprobante");
                            swal(
                                "Anulado!",
                                "La venta ha sido anulado con éxito.",
                                "success"
                            );
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                }
            });
        },
        //-------------OBTENER PRECIOS Y MABRIR_MODAL----------
        listarPrecio() {
            let me = this;
            var url = "/precios";
            axios
                .get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayPrecios = respuesta.precio.data;
                   
                    //me.precioCount = me.arrayBuscador.length;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
    
        cerrarModal2() {
    this.modal2 = false;
    this.reiniciarFormulario();
    // Restablecer campos específicos del modal
    this.tituloModal2 = "";
    this.idtipo_pago = "";
    this.tipoPago = "";
},
        cerrarModal3() {
            this.modal3 = false;
            this.tituloModal3 = "";
            this.numero_cuotas = "";
            this.tiempo_diaz = "";
            this.primera_cuota = false;
            this.cuotas = [];
        },

        calcularCambio() {
            const recibidoNumero = parseFloat(
                this.recibido / parseFloat(this.monedaVenta[0])
            );
            if (recibidoNumero === 0) {
                this.efectivo = recibidoNumero;
            
                this.cambio = 0;
                this.faltante = 0;
            } else if (recibidoNumero < this.calcularTotal) {
                this.efectivo = recibidoNumero;
                this.faltante = (this.calcularTotal - this.efectivo).toFixed(2);
            } else if (recibidoNumero === this.calcularTotal) {
                this.efectivo = recibidoNumero;
                this.cambio = 0;
                this.faltante = 0;
            } else {
                this.efectivo = recibidoNumero;
                this.cambio = (this.efectivo - this.calcularTotal).toFixed(2);
                this.faltante = 0;
            }
        },

        checkDocumento() {
            if (this.documento.length === 7 && /^\d+$/.test(this.documento)) {
                this.buscarClientePorDocumento();
            } else {
                this.resetearCampos();
            }
        },
        resetearCampos() {
            this.nombreCliente = "";
            this.nombreClienteEditable = false;
        },
        buscarClientePorDocumento() {
            axios
                .get(`/api/clientes?documento=${this.documento}`)
                .then((response) => {
                    const cliente = response.data;
                    console.log(cliente);
                    this.nombreCliente = cliente.nombre;
                    this.nombreClienteEditable = false;
                })
                .catch((error) => {
                    if (error.response && error.response.status === 404) {
                        this.nombreCliente = "";
                        this.nombreClienteEditable = true;
                        Swal.fire({
                            title: "Cliente no encontrado",
                            text: "No se encontró ningún cliente con el documento proporcionado.",
                            icon: "warning",
                            confirmButtonText: "Ok",
                        });
                    } else {
                        console.error("Error al buscar el cliente:", error);
                        this.resetearCampos();
                        Swal.fire({
                            title: "Error",
                            text: "Hubo un problema al buscar el cliente. Por favor, inténtelo de nuevo más tarde.",
                            icon: "error",
                            confirmButtonText: "Ok",
                        });
                    }
                });
        },
    },
    created() {
        this.listarPrecio();
    },
    mounted() {
        axios
            .get("/tu-endpoint-api")
            .then((response) => {
                this.arraySeleccionado = response.data;
            })
            .catch((error) => {
                console.error("Error al cargar los datos:", error);
            });
        this.datosConfiguracion();
        this.selectAlmacen();
        this.listarVenta(1, this.buscar, this.criterio);
        //this.obtenerDatosUsuario();
        this.actualizarFechaHora();
        this.ejecutarFlujoCompleto();
        document.addEventListener("keypress", this.handleKeyPress);
    },
    beforeDestroy() {
        document.removeEventListener("keypress", this.handleKeyPress);
    },
};
</script>
<style scoped>
.step-indicators {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.step {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.step.active {
    background-color: #007bff;
    color: white;
}

.step.completed {
    background-color: #28a745;
    color: white;
}

.product-detail {
    max-width: 1200px;
    margin: 0 auto;
}

.product-title {
    font-size: 2rem;
    margin-bottom: 1rem;
    text-align: center;
}

.image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1rem;
}

.product-image {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

.product-meta {
    margin-bottom: 1rem;
}

.stock-info {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.stock-info i {
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

.product-price {
    margin-bottom: 1rem;
}

.promo-price {
    color: var(--primary-color);
    font-size: 1.5rem;
}

.original-price {
    color: var(--text-color-secondary);
    margin-left: 0.5rem;
}

.regular-price {
    color: var(--text-color);
    font-size: 1.5rem;
}

.purchase-options {
    margin-bottom: 1rem;
}

.action-buttons {
    margin-top: 1rem;
}

.product-details-list {
    list-style-type: none;
    padding: 0;
}

.product-details-list li {
    margin-bottom: 0.5rem;
}

.footer {
    padding: 1rem;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.btn {
    display: flex;
    align-items: center;
}

.pi {
    margin-right: 0.5rem;
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

.form-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.form-group {
    flex: 1 1 100%;
    min-width: 250px;
}

@media (min-width: 768px) {
    .form-row {
        flex-wrap: nowrap;
    }

    .form-group {
        flex: 1;
    }
}

.p-inputgroup {
    width: 100%;
}

.p-float-label {
    width: 100%;
}

.p-inputtext,
.p-dropdown {
    width: 100%;
}
.payment-options {
  .custom-tabview {
    .p-tabview-nav {
      li {
        .p-tabview-nav-link {
          background-color: #f8f9fa;
          color: #495057;
          border: 1px solid #dee2e6;
          border-bottom: none;
          margin-right: 2px;
          border-top-left-radius: 4px;
          border-top-right-radius: 4px;
          transition: background-color 0.2s, color 0.2s;

          &:not(.p-disabled):focus {
            box-shadow: inset 0 0 0 0.2rem rgba(0,123,255,0.25);
          }
        }

        &.p-highlight .p-tabview-nav-link {
          background-color: #ffffff;
          color: #007bff;
          border-bottom: 2px solid #007bff;
        }
      }
    }

    .p-tabview-panels {
      background-color: #ffffff;
      border: 1px solid #dee2e6;
      border-top: none;
      border-bottom-left-radius: 4px;
      border-bottom-right-radius: 4px;
      padding: 1.5rem;
    }
  }

  .p-card {
    margin-bottom: 1rem;
  }

  .p-button-lg {
    width: 100%;
  }
}
</style>
