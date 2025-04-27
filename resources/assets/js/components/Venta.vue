<template>
  <main class="main">
    <!-- Breadcrumb -->
    <Panel>
      <Toast
        :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }"
        style="padding-top: 40px"
      >
      </Toast>
      <template #header>
        <div class="panel-header">
          <h4 class="panel-icon">Ventas</h4>
        </div>
      </template>
      <!-- Buscador -->
      <div class="p-d-flex p-ai-center p-mb-4">
        <Button
          @click="abrirTipoVenta"
          label="Nueva"
          icon="pi pi-plus"
          class="p-button-success-sm p-mr-2"
        />
        <span class="p-input-icon-left p-input-icon-right p-w-100">
          <i class="pi pi-search" />
          <InputText
            v-model="buscar"
            @input="buscarVenta"
            placeholder="Buscar venta..."
            class="p-inputtext-lg-sm moto-search p-w-100"
          />
          <i
            class="pi pi-times"
            v-if="buscar"
            @click="
              buscar = '';
              buscarVenta();
            "
            style="cursor: pointer"
          />
        </span>
      </div>

      <!-- Listado de Ventas -->
      <template v-if="listado == 1">
        <DataTable
          :value="arrayVenta"
          :rows="10"
          responsiveLayout="scroll"
          class="p-datatable-gridlines p-datatable-sm moto-table"
          :rowHover="true"
          dataKey="id"
        >
          <Column header="Opciones">
            <template #body="slotProps">
              <Button
                icon="pi pi-eye"
                @click="verVenta(slotProps.data.id)"
                class="p-button-sm p-mr-1"
                style="
                  background-color: green;
                  border-color: green;
                  color: white;
                "
              />
              <template
                v-if="slotProps.data.estado === 'Registrado' && idrol !== 2"
              >
                <Button
                  icon="pi pi-trash"
                  @click="desactivarVenta(slotProps.data.id)"
                  class="p-button-sm p-button-danger p-mr-1"
                />
              </template>
              <Button
                icon="pi pi-print"
                @click="
                  imprimirResivo(slotProps.data.id, slotProps.data.correo)
                "
                class="p-button-sm p-button-primary p-mr-1"
              />
              <Button
                v-if="slotProps.data.estado === 'Pendiente'"
                icon="pi pi-check-circle"
                @click="confirmarEntrega(slotProps.data.id)"
                class="p-button-sm p-button-success p-mr-1"
                tooltip="Confirmar Entrega"
              />
            </template>
          </Column>
          <Column field="usuario" header="Vendedor"></Column>
          <Column field="razonSocial" header="Cliente"></Column>
          <Column
            field="documentoid"
            header="Documento"
            class="d-none d-md-table-cell"
          ></Column>
          <Column
            field="num_comprobante"
            header="N° de Comprobante"
            class="d-none d-md-table-cell"
          ></Column>
          <Column
            field="fecha_hora"
            header="Fecha y Hora"
            class="d-none d-md-table-cell"
          ></Column>
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
          <Column field="estado" header="Estado" class="d-none d-md-table-cell">
            <template #body="slotProps">
              <Tag
                :severity="getEstadoSeverity(slotProps.data.estado)"
                :value="slotProps.data.estado"
              />
            </template>
          </Column>
        </DataTable>

        <Paginator
          :rows="10"
          :totalRecords="pagination.total"
          :first="(pagination.current_page - 1) * 10"
          @page="onPageChange"
        />
      </template>

      <!-- Ver Detalle de Venta -->
      <!-- Ver Detalle de Venta -->
      <template v-if="listado == 2">
        <Card class="shadow">
          <template #content>
            <!-- Encabezado con tipo de venta -->
            <div
              class="encabezado-venta"
              :class="{
                'bg-green-light':
                  ventaDetalle && ventaDetalle.idtipo_venta == 1,
                'bg-yellow-light':
                  ventaDetalle && ventaDetalle.idtipo_venta == 2,
                'bg-blue-light': ventaDetalle && ventaDetalle.idtipo_venta == 3,
              }"
            >
              <div class="tipo-venta">
                <i
                  class="pi mr-2"
                  :class="{
                    'pi-money-bill':
                      ventaDetalle && ventaDetalle.idtipo_venta == 1,
                    'pi-credit-card':
                      ventaDetalle && ventaDetalle.idtipo_venta == 2,
                    'pi-truck': ventaDetalle && ventaDetalle.idtipo_venta == 3,
                  }"
                ></i>
                <span v-if="ventaDetalle && ventaDetalle.idtipo_venta == 1"
                  >VENTA AL CONTADO</span
                >
                <span v-else-if="ventaDetalle && ventaDetalle.idtipo_venta == 2"
                  >VENTA A CRÉDITO</span
                >
                <span v-else-if="ventaDetalle && ventaDetalle.idtipo_venta == 3"
                  >VENTA ADELANTADA</span
                >
                <span v-else>DETALLE DE VENTA</span>
              </div>

              <div class="estado-venta">
                <Tag
                  v-if="ventaDetalle"
                  :severity="getEstadoSeverity(ventaDetalle.estado)"
                  :value="ventaDetalle.estado"
                ></Tag>
              </div>
            </div>

            <!-- Información principal del cliente y comprobante -->
            <div class="datos-principales">
              <div class="datos-item">
                <label>Cliente:</label>
                <div>{{ cliente }}</div>
              </div>
              <div class="datos-item">
                <label>Documento:</label>
                <div>{{ ventaDetalle ? ventaDetalle.num_documento : "-" }}</div>
              </div>
              <div class="datos-item">
                <label>Tipo Comprobante:</label>
                <div>{{ tipo_comprobante }}</div>
              </div>
              <div class="datos-item">
                <label>Número:</label>
                <div>{{ num_comprobante }}</div>
              </div>
              <div class="datos-item">
                <label>Fecha:</label>
                <div>
                  {{
                    ventaDetalle ? formatFecha(ventaDetalle.fecha_hora) : "-"
                  }}
                </div>
              </div>
            </div>

            <!-- Información específica según tipo de venta -->
            <!-- 1. Para ventas adelantadas -->
            <div
              v-if="ventaDetalle && ventaDetalle.idtipo_venta == 3"
              class="datos-especificos datos-adelantada"
            >
              <h4><i class="pi pi-truck"></i> Datos de entrega</h4>
              <div class="datos-adelantada-grid">
                <div class="datos-item">
                  <label><i class="pi pi-map-marker"></i> Dirección:</label>
                  <div>
                    {{ ventaDetalle.direccion_entrega || "No especificada" }}
                  </div>
                </div>
                <div class="datos-item">
                  <label><i class="pi pi-phone"></i> Teléfono:</label>
                  <div>
                    {{ ventaDetalle.telefono_contacto || "No especificado" }}
                  </div>
                </div>
                <div class="datos-item">
                  <label
                    ><i class="pi pi-calendar"></i> Fecha de entrega:</label
                  >
                  <div>
                    {{
                      ventaDetalle.fecha_entrega
                        ? formatFecha(ventaDetalle.fecha_entrega)
                        : "No especificada"
                    }}
                  </div>
                </div>
                <div
                  class="datos-item full-width"
                  v-if="ventaDetalle.observaciones"
                >
                  <label><i class="pi pi-comment"></i> Observaciones:</label>
                  <div>{{ ventaDetalle.observaciones }}</div>
                </div>
              </div>
            </div>

            <!-- 2. Para ventas a crédito -->
            <div
              v-if="ventaDetalle && ventaDetalle.idtipo_venta == 2"
              class="datos-especificos datos-credito"
            >
              <h4><i class="pi pi-credit-card"></i> Plan de pagos</h4>
              <div class="plan-pagos-header">
                <div class="datos-item">
                  <label>Total crédito:</label>
                  <div>
                    {{
                      creditoInfo
                        ? formatCurrency(creditoInfo.total)
                        : formatCurrency(total)
                    }}
                  </div>
                </div>
                <div class="datos-item">
                  <label>Número de cuotas:</label>
                  <div>
                    {{ creditoInfo ? creditoInfo.numero_cuotas : "N/A" }}
                  </div>
                </div>
                <div class="datos-item">
                  <label>Frecuencia (días):</label>
                  <div>
                    {{ creditoInfo ? creditoInfo.tiempo_dias_cuota : "N/A" }}
                  </div>
                </div>
              </div>

              <!-- Tabla de cuotas -->
              <DataTable
                :value="cuotasCredito"
                class="plan-pagos-table"
                v-if="cuotasCredito && cuotasCredito.length > 0"
              >
                <Column field="numero_cuota" header="N°"></Column>
                <Column field="fecha_pago" header="Fecha pago">
                  <template #body="slotProps">
                    {{ formatFecha(slotProps.data.fecha_pago) }}
                  </template>
                </Column>
                <Column field="precio_cuota" header="Monto">
                  <template #body="slotProps">
                    {{ formatCurrency(slotProps.data.precio_cuota) }}
                  </template>
                </Column>
                <Column field="estado" header="Estado">
                  <template #body="slotProps">
                    <Tag
                      :severity="
                        slotProps.data.estado === 'Pagado'
                          ? 'success'
                          : 'warning'
                      "
                      :value="slotProps.data.estado"
                    >
                    </Tag>
                  </template>
                </Column>
              </DataTable>
              <div v-else class="no-data">
                No hay información de cuotas disponible
              </div>
            </div>

            <!-- 3. Para ventas al contado -->
            <div
              v-if="ventaDetalle && ventaDetalle.idtipo_venta == 1"
              class="datos-especificos datos-contado"
            >
              <h4><i class="pi pi-money-bill"></i> Detalles del pago</h4>
              <div class="datos-contado-grid">
                <div class="datos-item">
                  <label>Método de pago:</label>
                  <div>{{ obtenerNombrePago(ventaDetalle.idtipo_pago) }}</div>
                </div>
                <div class="datos-item" v-if="ventaDetalle.monto_recibido">
                  <label>Monto recibido:</label>
                  <div>{{ formatCurrency(ventaDetalle.monto_recibido) }}</div>
                </div>
                <div class="datos-item" v-if="ventaDetalle.cambio">
                  <label>Cambio:</label>
                  <div>{{ formatCurrency(ventaDetalle.cambio) }}</div>
                </div>
              </div>
            </div>

            <!-- Listado de productos -->
            <h4 class="productos-titulo">
              <i class="pi pi-shopping-cart"></i> Productos
            </h4>
            <DataTable
              :value="arrayDetalle"
              class="productos-tabla"
              stripedRows
            >
              <Column field="articulo" header="Artículo"></Column>
              <Column header="Precio Unitario">
                <template #body="slotProps">
                  {{ formatCurrency(slotProps.data.precio) }}
                </template>
              </Column>
              <Column field="cantidad" header="Cantidad"></Column>
              <Column header="Subtotal">
                <template #body="slotProps">
                  <strong>{{
                    formatCurrency(
                      slotProps.data.precio * slotProps.data.cantidad
                    )
                  }}</strong>
                </template>
              </Column>
            </DataTable>

            <!-- Total -->
            <div class="total-section">
              <div class="total-label">Total:</div>
              <div class="total-amount">{{ formatCurrency(total) }}</div>
            </div>

            <!-- Botones de acción -->
            <div class="botones-accion">
              <Button
                v-if="ventaDetalle && ventaDetalle.idtipo_venta == 2"
                label="Ver plan completo"
                icon="pi pi-eye"
                class="p-button-info p-mr-2"
                @click="verPlanPagos(ventaDetalle.id)"
              >
              </Button>
              <Button
                label="Imprimir"
                icon="pi pi-print"
                class="p-button-secondary p-mr-2"
                @click="imprimirResivo(ventaDetalle ? ventaDetalle.id : 0)"
              >
              </Button>
              <Button
                label="Cerrar"
                icon="pi pi-times"
                class="p-button-danger"
                @click="ocultarDetalle()"
              >
              </Button>
            </div>
          </template>
        </Card>
      </template>
    </Panel>

    <!-- Modal para crear nueva venta -->
    <Dialog
      :visible.sync="modal2"
      :containerStyle="{ width: '300%', maxWidth: '850px' }"
      :modal="true"
      :closable="true"
      :closeOnEscape="false"
      @hide="cerrarModal2"
    >
      <template #header>
        <h5 class="modal-title">DETALLE VENTAS</h5>
      </template>
      <div class="p-fluid">
        <div class="p-field">
          <div class="step-indicators">
            <div class="step-container">
              <span
                :class="['step', { active: step === 1, completed: step > 1 }]"
                >1</span
              >
              <div class="step-label">Cliente</div>
            </div>
            <div
              class="step-line"
              :class="{ 'step-line-active': step >= 2 }"
            ></div>
            <div class="step-container">
              <span
                :class="['step', { active: step === 2, completed: step > 2 }]"
                >2</span
              >
              <div class="step-label">Productos</div>
            </div>
            <div
              class="step-line"
              :class="{ 'step-line-active': step >= 3 }"
            ></div>
            <div class="step-container">
              <span :class="['step', { active: step === 3 }]">3</span>
              <div class="step-label">Pago</div>
            </div>
          </div>
        </div>

        <!-- Step 1: Cliente -->
        <div v-if="step === 1" class="step-content p-fluid">
          <div class="form-container">
            <div class="form-row">
              <div class="form-group">
                <div class="p-inputgroup">
                  <span class="p-inputgroup-addon">
                    <i class="pi pi-id-card"></i>
                  </span>
                  <span class="p-float-label">
                    <InputText
                      id="documento"
                      v-model="documento"
                      @input="checkDocumento"
                      maxlength="7"
                      class="w-full"
                    />
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
                    <InputText
                      id="nombreCliente"
                      v-model="nombreCliente"
                      :disabled="!nombreClienteEditable"
                      class="w-full"
                    />
                    <label for="nombreCliente">Nombre del cliente</label>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <InputText v-model="idcliente" type="hidden" />
          <InputText v-model="tipo_documento" type="hidden" />
          <InputText v-model="complemento_id" type="hidden" />
          <InputText v-model="usuarioAutenticado" type="hidden" />
          <InputText v-model="puntoVentaAutenticado" type="hidden" />
          <InputText v-model="email" type="hidden" />
          <InputText v-model="num_comprob" type="hidden" disabled />
        </div>

        <!-- Step 2: Selección de productos -->
        <div v-if="step === 2" class="step-content">
          <!-- Header Section -->
          <div class="p-fluid p-grid">
            <div class="p-col-12 p-md-6">
              <label class="p-d-block"
                >Almacen <span class="p-error">*</span></label
              >
              <Dropdown
                v-model="selectedAlmacen"
                :options="arrayAlmacenes"
                optionLabel="nombre_almacen"
                optionValue="id"
                placeholder="Seleccione un almacén"
                @change="getAlmacenProductos"
              />
            </div>

            <div class="p-col-12 p-md-6">
              <label class="p-d-block">Buscar articulo</label>
              <div class="p-inputgroup">
                <InputText
                  v-model="codigo"
                  placeholder="Codigo del articulo"
                  :disabled="!selectedAlmacen"
                  @keyup="buscarArticulo()"
                />
                <Button
                  icon="pi pi-search"
                  :disabled="!selectedAlmacen"
                  @click="abrirModal"
                />
              </div>
            </div>
          </div>

          

          <!-- Cart Table -->
          <div class="p-mt-4">
            <DataTable :value="arrayDetalle" class="p-mt-3">
              <Column header="Opciones" style="width: 10%">
                <template #body="slotProps">
                  <Button
                    icon="pi pi-trash"
                    class="p-button-danger p-button-sm"
                    @click="eliminarDetalle(slotProps.data.id)"
                  />
                </template>
              </Column>
              <Column field="articulo" header="Artículo" style="width: 30%" />
              <Column
                field="precioUnidad"
                header="Precio Unidad"
                style="width: 15%"
              >
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
                  <InputNumber
                    v-model="slotProps.data.cantidad"
                    :min="1"
                    showButtons
                    buttonLayout="horizontal"
                    decrementButtonClass="p-button-danger p-button-sm"
                    incrementButtonClass="p-button-success p-button-sm"
                    incrementButtonIcon="pi pi-plus"
                    decrementButtonIcon="pi pi-minus"
                  />
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
          <div class="p-grid p-mt-2 p-justify-end">
  <div class="p-col-12 p-md-12 p-text-right" style="line-height: 1.2">
    <span style="font-size: 1.5rem; font-weight: 500;">Total Neto: </span>
    <strong style="font-size: 1.8rem; color: #2c3e50;">
      {{ (calcularTotal * parseFloat(monedaVenta[0])).toFixed(2) }} {{ monedaVenta[1] }}
    </strong>
  </div>
</div>
        </div>

        <!-- Step 3: Método de pago -->
        <div v-show="step === 3" class="step-content">
          <div class="p-d-flex p-jc-center p-mb-3">
            <div v-if="!tipoVentaSeleccionado" class="p-d-flex">
              <Button
                class="p-button-lg p-mr-3"
                :class="{
                  'p-button-primary': tipoVenta === 'contado',
                  'p-button-outlined': tipoVenta !== 'contado',
                }"
                @click="seleccionarTipoVenta('contado')"
              >
                <template #default>
                  <div class="p-d-flex p-flex-column p-ai-center">
                    <i
                      class="pi pi-money-bill p-mr-2"
                      style="font-size: 2rem"
                    ></i>
                  </div>
                  <span>Contado</span>
                </template>
              </Button>
              <Button
                class="p-button-lg p-mr-3"
                :class="{
                  'p-button-primary': tipoVenta === 'credito',
                  'p-button-outlined': tipoVenta !== 'credito',
                }"
                @click="seleccionarTipoVenta('credito')"
              >
                <template #default>
                  <div class="p-d-flex p-flex-column p-ai-center">
                    <i
                      class="pi pi-credit-card p-mb-2"
                      style="font-size: 2rem"
                    ></i>
                  </div>
                  <span>Crédito</span>
                </template>
              </Button>
              <Button
                class="p-button-lg"
                :class="{
                  'p-button-primary': tipoVenta === 'adelantada',
                  'p-button-outlined': tipoVenta !== 'adelantada',
                }"
                @click="seleccionarTipoVenta('adelantada')"
              >
                <template #default>
                  <div class="p-d-flex p-flex-column p-ai-center">
                    <i class="pi pi-truck p-mb-2" style="font-size: 2rem"></i>
                  </div>
                  <span>Adelantada</span>
                </template>
              </Button>
            </div>
          </div>

         <!-- Modificación para la sección de pago en efectivo -->
<div v-if="tipoVenta === 'contado'" class="payment-options">
  <TabView class="custom-tabview">
    <TabPanel header="Efectivo">
      <div class="p-grid p-fluid">
        <div class="p-col-12 p-md-7">
          <Card>
            <template #content>
              <div class="p-fluid">
                <div class="p-field">
                  <label for="montoEfectivo">
                    <i class="pi pi-money-bill p-mr-2" /> Monto
                    Recibido:
                  </label>
                  <div class="p-inputgroup">
                    <span class="p-inputgroup-addon">{{
                      monedaVenta[1]
                    }}</span>
                    <InputNumber
                      id="montoEfectivo"
                      v-model="recibido"
                      placeholder="Ingrese el monto recibido"
                      :class="{ 'p-invalid': montoInvalido }"
                    />
                  </div>
                  <small class="p-error" v-if="montoInvalido">
                    El monto recibido debe ser mayor o igual al total
                    a pagar
                  </small>
                </div>
                <div class="p-field">
                  <label for="cambioRecibir">
                    <i class="pi pi-sync p-mr-2" /> Cambio a Entregar:
                  </label>
                  <InputText
                    id="cambioRecibir"
                    :value="calcularCambio"
                    readonly
                  />
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
                <span
                  ><i class="pi pi-dollar p-mr-2" /> Monto
                  Total:</span
                >
                <span class="p-text-bold">
                  {{ totalFormateado }} {{ monedaVenta[1] }}
                </span>
              </div>
              <div class="p-d-flex p-jc-between">
                <span
                  ><i class="pi pi-money-bill p-mr-2" /> Total a
                  Pagar:</span
                >
                <span class="p-text-bold p-text-xl">
                  {{ totalFormateado }} {{ monedaVenta[1] }}
                </span>
              </div>
            </template>
          </Card>
          <Button
            label="Registrar Pago"
            icon="pi pi-check"
            class="p-button-success p-mt-2 p-button-lg p-button-raised"
            @click="validarYRegistrarPago"
            :disabled="!montoValido"
          />
        </div>
      </div>
    </TabPanel>
    
    <!-- Nueva tab para pago con QR -->
    <TabPanel header="QR">
      <div class="p-grid p-fluid">
        <div class="p-col-12 p-md-7">
          <Card>
            <template #content>
              <div class="p-fluid">
                <div class="qr-payment-info p-mb-3">
                  <h5><i class="pi pi-qrcode p-mr-2"></i> Pago con QR</h5>
                  <p class="p-mt-2">
                    1. Solicite al cliente que escanee el código QR para realizar el pago.
                  </p>
                  <p>
                    2. Verifique en su banca móvil que el pago se haya realizado correctamente.
                  </p>
                  <p>
                    3. Una vez confirmado el pago, presione "Confirmar Pago QR" para registrar la venta.
                  </p>
                </div>
                <div class="qr-verification-panel p-mt-3">
                  <div class="p-field">
                    <label>
                      <i class="pi pi-check-circle p-mr-2"></i> Verificación de Pago
                    </label>
                    <div class="p-formgroup-inline p-mt-2">
                      <div class="p-field-checkbox">
                        <Checkbox v-model="qrPagoVerificado" binary id="verificacion-qr" />
                        <label for="verificacion-qr">He verificado que el pago QR se ha realizado correctamente</label>
                      </div>
                    </div>
                  </div>
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
                <span><i class="pi pi-dollar p-mr-2"></i> Monto Total:</span>
                <span class="p-text-bold">
                  {{ totalFormateado }} {{ monedaVenta[1] }}
                </span>
              </div>
              <div class="p-d-flex p-jc-between">
                <span><i class="pi pi-mobile p-mr-2"></i> Total a Pagar QR:</span>
                <span class="p-text-bold p-text-xl">
                  {{ totalFormateado }} {{ monedaVenta[1] }}
                </span>
              </div>
            </template>
          </Card>
          <Button
            label="Confirmar Pago QR"
            icon="pi pi-check"
            class="p-button-success p-mt-2 p-button-lg p-button-raised"
            @click="registrarVentaQR"
            :disabled="!qrPagoVerificado"
          />
        </div>
      </div>
    </TabPanel>
  </TabView>
</div>

          <div v-if="tipoVenta === 'credito'">
            <div class="p-grid">
              <div class="p-col-4">
                <label for="numeroCuotas" class="font-weight-bold"
                  >Cantidad de cuotas <span class="p-error">*</span></label
                >
                <InputNumber
                  id="numeroCuotas"
                  v-model="numero_cuotas"
                  :useGrouping="false"
                />
              </div>

              <div class="p-col-4">
                <label for="tiempoDias" class="font-weight-bold"
                  >Frecuencia de Pagos <span class="p-error">*</span></label
                >
                <div class="p-inputgroup">
                  <InputNumber
                    id="tiempoDias"
                    v-model="tiempo_diaz"
                    :useGrouping="false"
                  />
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
                  <Button
                    label="GENERAR CUOTAS"
                    @click="generarCuotas"
                    class="p-button-success"
                  />
                </div>
              </div>
            </div>
            <DataTable
              :value="cuotas"
              class="p-mt-4"
              :paginator="true"
              :rows="10"
              responsiveLayout="scroll"
            >
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
              <Column field="saldo_restante" header="Saldo">
                <template #body="slotProps">
                  {{
                    (
                      slotProps.data.saldo_restante * parseFloat(monedaVenta[0])
                    ).toFixed(2)
                  }}
                  {{ monedaVenta[1] }}
                </template>
              </Column>
              <Column field="estado" header="Estado" />
            </DataTable>

            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                @click="cerrarModal2()"
              >
                Volver
              </button>
              <button
                type="button"
                class="btn btn-primary"
                @click="registrarVenta()"
              >
                Registrar
              </button>
            </div>
          </div>

          <!-- Inicio - Sección de Venta Adelantada -->
          <div v-if="tipoVenta === 'adelantada'" class="payment-options">
            <div class="p-grid">
              <div class="p-col-12 p-md-7">
                <Card>
                  <template #content>
                    <h5>Datos de Entrega</h5>
                    <div class="p-fluid">
                      <div class="p-field">
                        <label for="direccionEntrega">
                          <i class="pi pi-map-marker p-mr-2" /> Dirección de
                          Entrega:
                        </label>
                        <InputText
                          id="direccionEntrega"
                          v-model="direccionEntrega"
                          placeholder="Ingrese la dirección de entrega"
                          class="w-full"
                        />
                      </div>
                      <div class="p-field">
                        <label for="telefonoContacto">
                          <i class="pi pi-phone p-mr-2" /> Teléfono de Contacto:
                        </label>
                        <InputText
                          id="telefonoContacto"
                          v-model="telefonoContacto"
                          placeholder="Teléfono para coordinar entrega"
                          class="w-full"
                        />
                      </div>
                      <div class="p-field">
                        <label for="fechaEntrega">
                          <i class="pi pi-calendar p-mr-2" /> Fecha de Entrega:
                        </label>
                        <Calendar
                          id="fechaEntrega"
                          v-model="fechaEntrega"
                          :minDate="new Date()"
                          dateFormat="dd/mm/yy"
                          placeholder="Seleccione fecha"
                          class="w-full"
                        />
                      </div>
                      <div class="p-field">
                        <label for="observaciones">
                          <i class="pi pi-comment p-mr-2" /> Observaciones:
                        </label>
                        <Textarea
                          id="observaciones"
                          v-model="observaciones"
                          placeholder="Instrucciones adicionales para la entrega"
                          rows="3"
                          class="w-full"
                        />
                      </div>
                    </div>
                  </template>
                </Card>
              </div>
              <div class="p-col-12 p-md-5">
                <Card>
                  <template #content>
                    <h5>Detalles de Pago</h5>
                    <div class="p-d-flex p-jc-between p-mb-2">
                      <span
                        ><i class="pi pi-dollar p-mr-2" /> Monto Total:</span
                      >
                      <span class="p-text-bold">
                        {{ totalFormateado }} {{ monedaVenta[1] }}
                      </span>
                    </div>
                    <div class="p-field p-mt-3">
                      <label for="tipoPagoAdelantado" class="font-weight-bold">
                        Método de Pago:
                      </label>
                      <Dropdown
                        id="tipoPagoAdelantado"
                        v-model="tipoPagoAdelantado"
                        :options="opcionesPagoAdelantado"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione método de pago"
                        class="w-full"
                      />
                    </div>
                    <div
                      class="p-field"
                      v-if="tipoPagoAdelantado === 'efectivo'"
                    >
                      <label for="montoAdelantado">
                        <i class="pi pi-money-bill p-mr-2" /> Monto Recibido:
                      </label>
                      <div class="p-inputgroup">
                        <span class="p-inputgroup-addon">{{
                          monedaVenta[1]
                        }}</span>
                        <InputNumber
                          id="montoAdelantado"
                          v-model="montoAdelantado"
                          placeholder="Ingrese el monto recibido"
                          :class="{ 'p-invalid': montoAdelantadoInvalido }"
                        />
                      </div>
                      <small class="p-error" v-if="montoAdelantadoInvalido">
                        El monto recibido debe ser mayor o igual al total a
                        pagar
                      </small>
                    </div>
                    <div
                      class="p-field"
                      v-if="tipoPagoAdelantado === 'efectivo'"
                    >
                      <label for="cambioAdelantado">
                        <i class="pi pi-sync p-mr-2" /> Cambio a Entregar:
                      </label>
                      <InputText
                        id="cambioAdelantado"
                        :value="calcularCambioAdelantado"
                        readonly
                      />
                    </div>
                  </template>
                </Card>
                <Button
                  label="Registrar Pedido"
                  icon="pi pi-check"
                  class="p-button-success p-mt-2 p-button-lg p-button-raised"
                  @click="validarYRegistrarVentaAdelantada"
                  :disabled="!datosAdelantadosValidos"
                />
              </div>
            </div>
          </div>
          <!-- Fin - Sección de Venta Adelantada -->
        </div>

        <footer class="footer d-flex justify-content-center gap-2">
          <Button
            @click="prevStep"
            :disabled="step === 1"
            class="p-button-primary"
            icon="pi pi-chevron-left"
            iconPos="left"
            label="Anterior"
            :style="{ width: '150px' }"
          />
          <Button
            @click="validarYAvanzar"
            :disabled="step === 3"
            class="p-button-primary"
            icon="pi pi-chevron-right"
            iconPos="right"
            label="Siguiente"
            :style="{ width: '150px' }"
          />
        </footer>
      </div>
    </Dialog>
    <div class="modal" tabindex="-1" v-if="arraySeleccionado" 
     :style="{display: isDetailModalVisible ? 'flex !important' : 'none'}" 
     style="align-items: center; justify-content: center; background-color: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 3250;">
  
  <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 850px; margin: auto;">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header justify-content-center">
  <h5 class="modal-title fs-4 fw-bold text-center">{{ arraySeleccionado.nombre }}</h5>
</div>


      <!-- Body (mantener el contenido existente) -->
      <div class="modal-body">
        <div class="row g-3">
          <!-- Columna izquierda: Imagen y stock -->
          <div class="col-md-4">
            <div class="mb-3 text-center">
              <img
                :src="arraySeleccionado.fotografia ? `img/articulo/${arraySeleccionado.fotografia}?t=${new Date().getTime()}` : 'img/productoSinImagen.png'"
                :alt="arraySeleccionado.nombre"
                class="img-fluid rounded"
                style="max-height: 200px;"
              />
            </div>

            <!-- Stock disponible -->
            <div class="alert mb-3" 
                 :class="{
                   'alert-success': arraySeleccionado.saldo_stock / (unidadPaquete || 1) - cantidad > arraySeleccionado.stock / (unidadPaquete || 1),
                   'alert-warning': arraySeleccionado.saldo_stock / (unidadPaquete || 1) - cantidad <= arraySeleccionado.stock / (unidadPaquete || 1),
                   'alert-danger': arraySeleccionado.saldo_stock / (unidadPaquete || 1) - cantidad <= 0
                 }">
              <div class="d-flex align-items-center">
                <i class="bi bi-box-seam me-2"></i>
                <span class="fw-bold">Stock disponible:</span>
              </div>
              <div class="ms-4">
                {{ arraySeleccionado.saldo_stock / (unidadPaquete || 1) - cantidad }}
                {{ unidadPaquete == 1 ? "Unidades" : "Paquetes" }}
              </div>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
              <h6 class="fw-bold mb-2">
                <i class="bi bi-text-paragraph me-2"></i>
                Descripción
              </h6>
              <p class="text-muted">
                {{ arraySeleccionado.descripcion || "No hay descripción disponible para este producto." }}
              </p>
            </div>
          </div>

          <!-- Columna central: Precios -->
          <div class="col-md-4">
            <div class="mb-4">
              <h6 class="fw-bold mb-3">
                <i class="bi bi-tag me-2"></i>
                PRECIOS
              </h6>
              <div class="list-group">
                <div v-for="(precio, key) in [
                  { nombre: 'Precio Uno', valor: arraySeleccionado.precio_uno },
                  { nombre: 'Precio Dos', valor: arraySeleccionado.precio_dos },
                  { nombre: 'Precio Tres', valor: arraySeleccionado.precio_tres },
                  { nombre: 'Precio Cuatro', valor: arraySeleccionado.precio_cuatro },
                  { nombre: 'Precio Venta', valor: arraySeleccionado.precio_venta },
                ]" :key="key" class="list-group-item border-0 px-0 py-1" v-if="precio.valor !== '0.0000'">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" 
                           :id="'precio_'+key" 
                           :value="precio.valor" 
                           v-model="precioSeleccionado"
                           @change="seleccionarPrecio(precio.valor)">
                    <label class="form-check-label" :for="'precio_'+key">
                      {{ precio.nombre }}: {{ formatearPrecio(precio.valor) }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Columna derecha: Detalles -->
          <div class="col-md-4">
            <div class="mb-3">
              <h6 class="fw-bold mb-3">
                <i class="bi bi-info-circle me-2"></i>
                DETALLES DEL PRODUCTO
              </h6>
              <dl class="row">
                <dt class="col-sm-5">Código:</dt>
                <dd class="col-sm-7">{{ arraySeleccionado.codigo || "No especificado" }}</dd>

                <dt class="col-sm-5">Categoría:</dt>
                <dd class="col-sm-7">{{ arraySeleccionado.nombre_categoria || "No especificada" }}</dd>

                <dt class="col-sm-5">Marca:</dt>
                <dd class="col-sm-7">{{ arraySeleccionado.nombre_marca || "No especificada" }}</dd>

                <dt class="col-sm-5">Industria:</dt>
                <dd class="col-sm-7">{{ arraySeleccionado.nombre_industria || "No especificada" }}</dd>

                <dt class="col-sm-5">Grupo:</dt>
                <dd class="col-sm-7">{{ arraySeleccionado.nombre_grupo || "No especificado" }}</dd>

                <dt class="col-sm-5">Medida:</dt>
                <dd class="col-sm-7">{{ arraySeleccionado.nombre_medida || "No especificada" }}</dd>

                <template v-if="arraySeleccionado.unidad_envase">
                  <dt class="col-sm-5">cantidad bpaquete:</dt>
                  <dd class="col-sm-7">{{ arraySeleccionado.unidad_envase }}</dd>
                </template>

                <template v-if="arraySeleccionado.fecha_vencimiento">
                  <dt class="col-sm-5">Vencimiento:</dt>
                  <dd class="col-sm-7">{{ new Date(arraySeleccionado.fecha_vencimiento).toLocaleDateString() }}</dd>
                </template>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <span class="text-muted me-2">Precio:</span>
          <span :class="precioSeleccionado ? 'fw-bold text-success' : 'text-muted'">
            {{ precioSeleccionado ? formatearPrecio(precioSeleccionado) : 'No seleccionado' }}
          </span>
        </div>

        <div class="d-flex align-items-center">
          <select class="form-select form-select-sm me-2" 
                  v-model="unidadPaquete" 
                  @change="actualizarVistaStock"
                  style="width: auto;">
            <option v-for="option in tipoVentaOptions" 
                    :value="option.value" 
                    :key="option.value">
              {{ option.label }}
            </option>
          </select>

          <div class="input-group me-2" style="width: 120px;">
            <button class="btn btn-outline-danger" type="button" @click="cantidad > 1 ? cantidad-- : null">
              <i class="bi bi-dash"></i>
            </button>
            <input type="text" class="form-control text-center" v-model="cantidad" min="1">
            <button class="btn btn-outline-success" type="button" @click="cantidad++">
              <i class="bi bi-plus"></i>
            </button>
          </div>

          <button class="btn btn-success me-2" @click="agregarDetalleYCerrar()">
            <i class="bi bi-cart-plus me-1"></i> Agregar
          </button>
          <button class="btn btn-danger" @click="limpiarSeleccion">
            <i class="bi bi-trash me-1"></i> Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
    <!-- Modal de búsqueda de artículos -->
    <Dialog
      :visible.sync="modal"
      :containerStyle="{ width: '100%', maxWidth: '800px', paddingTop: '35px' }"
      modal
      closable
    >
      <template #header>
        <h3>{{ tituloModal }}</h3>
      </template>

      <TabView>
        <TabPanel header="Articulos">
          <div class="grid">
            <div class="col-12 md:col-6 lg:col-6">
              <span class="p-input-icon-left w-full">
                <i class="pi pi-search" />
                <InputText
                  v-model="buscarA"
                  placeholder="Buscar por código o nombre"
                  @input="listarArticulo"
                  class="w-full"
                />
              </span>
            </div>
          </div>
          <DataTable
    :value="arrayArticulo"
    :paginator="true"
    :rows="10"
    class="p-datatable-sm moto-table"
    @row-select="onRowSelect"
    selectionMode="single"
    responsiveLayout="scroll"
    :selection="selectedArticulo"
    breakpoint="960px"
>
    <Column field="codigo" header="Código" :class="'sm:table-cell'" />
    <Column field="nombre" header="Nombre" :class="'sm:table-cell'" />
    <Column 
        field="nombre_marca" 
        header="Marca" 
        :class="'hidden sm:table-cell'" 
    />
    <Column
        field="nombre_categoria"
        header="Categoría"
        :class="'hidden sm:table-cell'"
    />
    <Column header="Precio Venta" :class="'hidden sm:table-cell'">
        <template #body="slotProps">
            {{
                (
                    slotProps.data.precio_venta * parseFloat(monedaVenta[0])
                ).toFixed(2)
            }}
            {{ monedaVenta[1] }}
        </template>
    </Column>
    <Column
        field="saldo_stock"
        header="Stock"
        :class="'sm:table-cell'"
    />
    <Column header="Estado" :class="'hidden sm:table-cell'">
        <template #body="slotProps">
            <Tag
                :severity="slotProps.data.condicion ? 'success' : 'danger'"
                :value="slotProps.data.condicion ? 'Activo' : 'Desactivado'"
            />
        </template>
    </Column>
</DataTable>
        </TabPanel>
      </TabView>
    </Dialog>
    <Dialog
      :visible.sync="mostrarDetalleCredito"
      :style="{ width: '80%' }"
      :modal="true"
      :closable="true"
      :closeOnEscape="true"
      @hide="mostrarDetalleCredito = false"
    >
      <template #header>
        <h4>Plan de Pagos Completo</h4>
      </template>

      <div v-if="creditoInfo" class="info-credito">
        <div class="info-credito-item">
          <label>Total crédito:</label>
          <div>{{ formatCurrency(creditoInfo.total) }}</div>
        </div>
        <div class="info-credito-item">
          <label>Número de cuotas:</label>
          <div>{{ creditoInfo.numero_cuotas }}</div>
        </div>
        <div class="info-credito-item">
          <label>Frecuencia (días):</label>
          <div>{{ creditoInfo.tiempo_dias_cuota }}</div>
        </div>
        <div class="info-credito-item">
          <label>Próximo pago:</label>
          <div>{{ formatFecha(creditoInfo.proximo_pago) }}</div>
        </div>
        <div class="info-credito-item">
          <label>Estado:</label>
          <div>
            <Tag
              :severity="
                creditoInfo.estado === 'Completado' ? 'success' : 'warning'
              "
              :value="creditoInfo.estado"
            >
            </Tag>
          </div>
        </div>
      </div>

      <DataTable
        :value="cuotasCredito"
        class="plan-pagos-table"
        v-if="cuotasCredito && cuotasCredito.length > 0"
      >
        <Column field="numero_cuota" header="N°"></Column>
        <Column field="fecha_pago" header="Fecha pago">
          <template #body="slotProps">
            {{ formatFecha(slotProps.data.fecha_pago) }}
          </template>
        </Column>
        <Column field="precio_cuota" header="Monto">
          <template #body="slotProps">
            {{ formatCurrency(slotProps.data.precio_cuota) }}
          </template>
        </Column>
        <Column field="totalCancelado" header="Pagado">
          <template #body="slotProps">
            {{ formatCurrency(slotProps.data.totalCancelado) }}
          </template>
        </Column>
        <Column field="saldo_restante" header="Saldo">
          <template #body="slotProps">
            {{ formatCurrency(slotProps.data.saldo_restante) }}
          </template>
        </Column>
        <Column field="fecha_cancelado" header="Fecha pago">
          <template #body="slotProps">
            {{
              slotProps.data.fecha_cancelado
                ? formatFecha(slotProps.data.fecha_cancelado)
                : "Pendiente"
            }}
          </template>
        </Column>
        <Column field="estado" header="Estado">
          <template #body="slotProps">
            <Tag
              :severity="
                slotProps.data.estado === 'Pagado' ? 'success' : 'warning'
              "
              :value="slotProps.data.estado"
            >
            </Tag>
          </template>
        </Column>
      </DataTable>
      <div v-else class="no-data">No hay información de cuotas disponible</div>

      <template #footer>
        <Button
          label="Cerrar"
          icon="pi pi-times"
          @click="mostrarDetalleCredito = false"
          class="p-button-danger"
        ></Button>
      </template>
    </Dialog>
  </main>
</template>

<script>
import Button from "primevue/button";
import Calendar from "primevue/calendar";
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Dialog from "primevue/dialog";
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Paginator from "primevue/paginator";
import Panel from "primevue/panel";
import TabPanel from "primevue/tabpanel";
import TabView from "primevue/tabview";
import Tag from "primevue/tag";
import Textarea from "primevue/textarea";
import Toast from "primevue/toast";
import Swal from "sweetalert2";

export default {
  components: {
    Button,
    Card,
    Calendar,
    Column,
    DataTable,
    Dialog,
    Dropdown,
    InputNumber,
    InputText,
    Paginator,
    Panel,
    TabPanel,
    TabView,
    Tag,
    Textarea,
    Toast,
  },

  data() {
    return {
      qrPagoVerificado: true,
      ventaDetalle: null,
      creditoInfo: null,
      cuotasCredito: [],
      arrayDetalle: [],
      cliente: "",
      tipo_comprobante: "",
      num_comprobante: "",
      total: 0,
      cargando: false,
      mostrarDetalleCredito: false,
      listado: 1,
      // Configuración y estado general
      isDetailModalVisible: false,
      idrol: null,
      step: 1,
      modal2: false,
      modal: false,
      tipoVenta: "",
      tipoVentaSeleccionado: false,
      monedaVenta: [], // [valor, símbolo]
      saldosNegativos: 1,
      mostrarSpinner: false,
      impuesto: 0.18, // Añadido valor por defecto para el impuesto

      // Búsqueda y listados
      buscar: "",
      buscarA: "",
      pagination: {
        total: 0,
        current_page: 1,
        last_page: 0,
      },

      // Ventas
      arrayVenta: [],
      listado: 1,
      tituloModal: "",

      // Cliente
      cliente: "",
      idcliente: 0,
      nombreCliente: "",
      nombreClienteEditable: false,
      documento: "",
      tipo_documento: "1",
      complemento_id: "",
      email: "",
      usuarioAutenticado: null,
      puntoVentaAutenticado: null,

      // Productos y carrito
      arrayDetalle: [],
      arraySeleccionado: null,
      selectedArticulo: null,
      arrayArticulo: [],
      codigo: "",
      cantidad: 1,
      precioSeleccionado: null,
      descuentoProducto: 0,
      unidadPaquete: "1", // Por defecto, establecido a '1' para venta por unidad
      tipoVentaOptions: [
        { label: "Por unidad", value: "1" },
        { label: "Por paquete", value: "paquete" },
      ],

      // Almacenes
      arrayAlmacenes: [],
      selectedAlmacen: null,
      idAlmacen: null,

      // Comprobante
      tipo_comprobante: "RESIVO",
      serie_comprobante: "",
      num_comprobante: "",
      num_comprob: "",
      last_comprobante: 0,
      total: 0.0,

      // Pago contado
      recibido: null,
      montoInvalido: false,
      idtipo_pago: 1,

      // Crédito
      idtipo_venta: 1,
      tiempo_diaz: "",
      numero_cuotas: "",
      cuotas: [],
      primer_precio_cuota: 0,
      primera_cuota: false,

      // Venta adelantada
      direccionEntrega: "",
      telefonoContacto: "",
      fechaEntrega: null,
      observaciones: "",
      tipoPagoAdelantado: null,
      montoAdelantado: null,
      montoAdelantadoInvalido: false,
      opcionesPagoAdelantado: [
        { label: "Efectivo", value: "efectivo" },
        { label: "Transferencia", value: "transferencia" },
        { label: "Tarjeta", value: "tarjeta" },
        { label: "QR", value: "qr" },
      ],
    };
  },
  watch: {
    recibido(newValue) {
      this.montoInvalido = newValue !== null && !this.montoValido;
    },
    montoAdelantado(newValue) {
      this.montoAdelantadoInvalido =
        newValue !== null && !this.montoAdelantadoValido;
    },
    codigo(newValue) {
      if (newValue) {
        this.buscarArticulo();
      }
    },
    documento(newDocumento) {
      if (newDocumento.length === 7) {
        this.checkDocumento();
      }
    },
  },
  computed: {
    totalFormateado() {
      return (this.calcularTotal * parseFloat(this.monedaVenta[0])).toFixed(2);
    },
    calcularCambio() {
      if (!this.recibido) return "0.00";
      return (
        this.recibido -
        this.calcularTotal * parseFloat(this.monedaVenta[0])
      ).toFixed(2);
    },
    calcularCambioAdelantado() {
      if (!this.montoAdelantado) return "0.00";
      return (
        this.montoAdelantado -
        this.calcularTotal * parseFloat(this.monedaVenta[0])
      ).toFixed(2);
    },
    montoValido() {
      if (!this.recibido) return false;
      return (
        this.recibido >= this.calcularTotal * parseFloat(this.monedaVenta[0])
      );
    },
    montoAdelantadoValido() {
      if (!this.montoAdelantado) return false;
      return (
        this.montoAdelantado >=
        this.calcularTotal * parseFloat(this.monedaVenta[0])
      );
    },
    datosAdelantadosValidos() {
      if (
        !this.direccionEntrega ||
        !this.telefonoContacto ||
        !this.fechaEntrega ||
        !this.tipoPagoAdelantado
      ) {
        return false;
      }

      if (this.tipoPagoAdelantado === "efectivo") {
        return (
          this.montoAdelantado >=
          this.calcularTotal * parseFloat(this.monedaVenta[0])
        );
      }

      return true;
    },
    calcularStockDisponible() {
      if (!this.arraySeleccionado) return 0;

      const stockTotal = parseInt(this.arraySeleccionado.saldo_stock);
      const unidadEnvase = parseInt(this.arraySeleccionado.unidad_envase || 1);

      if (this.unidadPaquete === "paquete") {
        return Math.floor(stockTotal / unidadEnvase);
      } else {
        return stockTotal;
      }
    },
    calcularTotal() {
      let resultado = 0.0;
      for (let i = 0; i < this.arrayDetalle.length; i++) {
        resultado +=
          this.arrayDetalle[i].precioseleccionado *
          this.arrayDetalle[i].cantidad;
      }
      return resultado;
    },
  },
  methods: {
    registrarVentaQR() {
    if (!this.qrPagoVerificado) {
      Swal.fire({
        icon: 'warning',
        title: 'Verificación requerida',
        text: 'Debe verificar que el pago QR se ha realizado correctamente antes de continuar.'
      });
      return;
    }
    
    // El método llama al mismo registrarVenta pero con el ID de tipo de pago para QR (4)
    this.registrarVenta(4); // 4 es el ID para tipo de pago QR
  },
    limpiarSeleccion() {
      // Set arraySeleccionado to null to close the modal
      this.arraySeleccionado = null;
      // Reset the search field
      this.codigo = "";
      // Reset other related values
      this.cantidad = 1;
      this.unidadPaquete = "1";
      this.precioSeleccionado = null;
    },

    // Método para buscar artículo con validación mejorada
    buscarArticulo() {
      clearTimeout(this.timer);
      this.timer = setTimeout(() => {
        if (!this.codigo || !this.selectedAlmacen) return;

        axios
          .get(
            `/articulo/buscarArticuloVenta?filtro=${this.codigo}&idalmacen=${this.selectedAlmacen}`
          )
          .then((response) => {
            if (response.data.articulos && response.data.articulos.length > 0) {
              this.arraySeleccionado = response.data.articulos[0];
              this.isDetailModalVisible = true; // Set visibility to true
            } else {
              // No article found, clear the search
              this.codigo = "";
              Swal.fire({
                icon: "warning",
                title: "Artículo no encontrado",
                text: "No se encontró ningún artículo con ese código",
              });
            }
          })
          .catch((error) => {
            console.error("Error al buscar artículo:", error);
          });
      }, 500);
    },

    // Método para determinar color de estado
    getEstadoSeverity(estado) {
      switch (estado) {
        case "Pendiente":
          return "warning";
        case "Entregado":
          return "success";
        case "Anulado":
          return "danger";
        default:
          return "info";
      }
    },

    // Método para confirmar entrega
    confirmarEntrega(id) {
      Swal.fire({
        title: "¿Confirmar entrega?",
        text: "Esta acción marcará la venta como Entregada",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Confirmar Entrega",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.value) {
          axios
            .put("/venta/confirmar-entrega", { id })
            .then(() => {
              this.listarVenta(1, this.buscar);
              Swal.fire(
                "Entrega Confirmada",
                "La venta ha sido marcada como Entregada",
                "success"
              );
            })
            .catch((error) => {
              console.error("Error al confirmar entrega:", error);
              Swal.fire("Error", "No se pudo confirmar la entrega", "error");
            });
        }
      });
    },

    // Modified method to add product and close modal in one step
    agregarDetalleYCerrar() {
      // Try to add the product directly without calling agregarDetalle
      if (this.encuentra(this.arraySeleccionado.id)) {
        Swal.fire({
          icon: "error",
          title: "Error...",
          text: "Este Artículo ya se encuentra agregado!",
        });
        return false;
      }

      if (!this.precioSeleccionado) {
        Swal.fire({
          icon: "error",
          title: "Error...",
          text: "Por favor, seleccione un precio antes de agregar",
        });
        return false;
      }

      const stockDisponible = this.calcularStockDisponible;
      const unidadEnvase = this.arraySeleccionado.unidad_envase || 1;
      const cantidadSolicitada =
        this.unidadPaquete === "paquete"
          ? this.cantidad
          : this.cantidad / unidadEnvase;

      if (this.saldosNegativos === 0 && stockDisponible < cantidadSolicitada) {
        Swal.fire({
          icon: "error",
          title: "Error...",
          text: "No hay stock disponible!",
        });
        return false;
      }

      const precioUnitario = parseFloat(this.precioSeleccionado);
      const cantidadTotal =
        this.unidadPaquete === "paquete"
          ? this.cantidad * unidadEnvase
          : this.cantidad;

      const nuevoDetalle = {
        id: Date.now(),
        idarticulo: this.arraySeleccionado.id,
        articulo: this.arraySeleccionado.nombre,
        medida: this.arraySeleccionado.medida,
        unidad_envase: unidadEnvase,
        cantidad: cantidadTotal,
        stock: this.arraySeleccionado.saldo_stock,
        precio: precioUnitario,
        precioseleccionado: precioUnitario,
      };

      this.arrayDetalle.push(nuevoDetalle);

      // Cerrar el modal estableciendo arraySeleccionado a null
      this.arraySeleccionado = null;
      this.cantidad = 1;
      this.unidadPaquete = "1";
      this.codigo = "";
      this.precioSeleccionado = null;

      return true;
    },

    // Navegación de pasos
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
    validarYAvanzar() {
      const errores = [];

      if (this.step === 1) {
      } else if (this.step === 2) {
        if (!this.selectedAlmacen) {
          errores.push("Seleccione un almacén");
        }
        if (this.arrayDetalle.length === 0) {
          errores.push("Añada al menos un artículo a la tabla");
        }
      }

      if (errores.length > 0) {
        Swal.fire("Campos incompletos", errores.join("\n"), "warning");
      } else {
        this.nextStep();
      }
    },

    // Gestión de clientes
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
          this.nombreCliente = cliente.nombre;
          this.idcliente = cliente.id;
          this.tipo_documento = cliente.tipo_documento;
          this.complemento_id = cliente.complemento_id;
          this.email = cliente.email;
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
          }
        });
    },
    async buscarOCrearCliente() {
      try {
        if (!this.documento || this.documento.length === 0) {
          this.documento = "000000";
          this.nombreCliente = "SIN NOMBRE";
        }

        // Primero, intenta buscar el cliente
        const response = await axios.get(
          `/api/clientes/existe?documento=${this.documento}`
        );

        if (response.data.existe) {
          // Si el cliente existe, usa ese ID
          this.idcliente = response.data.cliente.id;
        } else {
          // Si el cliente no existe, intenta crearlo
          const nuevoClienteResponse = await axios.post("/cliente/registrar", {
            nombre: this.nombreCliente || "SIN NOMBRE",
            num_documento: this.documento,
            tipo_documento: "5",
          });
          this.idcliente = nuevoClienteResponse.data.id;
        }
      } catch (error) {
        console.error("Error al buscar o crear cliente:", error);
      }
    },

    // Gestión de artículos
    seleccionarPrecio(precio) {
      this.precioSeleccionado = precio;
    },
    formatearPrecio(precio) {
      if (precio == null) return "N/A";

      let precioNumerico =
        typeof precio === "string" ? parseFloat(precio) : precio;
      if (isNaN(precioNumerico)) return "N/A";

      return `${precioNumerico.toFixed(2)} ${this.monedaVenta[1]}`;
    },
    actualizarVistaStock() {
      this.$forceUpdate();
    },

    // Gestión del carrito
    agregarDetalle() {
      if (this.encuentra(this.arraySeleccionado.id)) {
        Swal.fire({
          icon: "error",
          title: "Error...",
          text: "Este Artículo ya se encuentra agregado!",
        });
        return;
      }

      if (!this.precioSeleccionado) {
        Swal.fire({
          icon: "error",
          title: "Error...",
          text: "Por favor, seleccione un precio antes de agregar",
        });
        return;
      }

      const stockDisponible = this.calcularStockDisponible;
      const unidadEnvase = this.arraySeleccionado.unidad_envase || 1;
      const cantidadSolicitada =
        this.unidadPaquete === "paquete"
          ? this.cantidad
          : this.cantidad / unidadEnvase;

      if (this.saldosNegativos === 0 && stockDisponible < cantidadSolicitada) {
        Swal.fire({
          icon: "error",
          title: "Error...",
          text: "No hay stock disponible!",
        });
        return;
      }

      const precioUnitario = parseFloat(this.precioSeleccionado);
      const cantidadTotal =
        this.unidadPaquete === "paquete"
          ? this.cantidad * unidadEnvase
          : this.cantidad;

      const nuevoDetalle = {
        id: Date.now(),
        idarticulo: this.arraySeleccionado.id,
        articulo: this.arraySeleccionado.nombre,
        medida: this.arraySeleccionado.medida,
        unidad_envase: unidadEnvase,
        cantidad: cantidadTotal,
        stock: this.arraySeleccionado.saldo_stock,
        precio: precioUnitario, // Aseguramos que el precio esté incluido
        precioseleccionado: precioUnitario,
      };

      this.arrayDetalle.push(nuevoDetalle);

      // Limpiar selección
      this.arraySeleccionado = [];
      this.cantidad = 1;
      this.unidadPaquete = "1";
      this.codigo = "";
      this.precioSeleccionado = null;
    },
    eliminarDetalle(id) {
      const index = this.arrayDetalle.findIndex((item) => item.id === id);
      if (index !== -1) {
        this.arrayDetalle.splice(index, 1);
      }
    },
    eliminarSeleccionado() {
      this.codigo = "";
      this.arraySeleccionado = [];
    },
    encuentra(id) {
      return this.arrayDetalle.some((item) => item.idarticulo === id);
    },
    onRowSelect(event) {
      this.agregarDetalleModal(event.data);
    },
    agregarDetalleModal(data) {
      this.codigo = data.codigo;
      this.precioSeleccionado = data.precio_uno;
      this.unidadPaquete = "1";
      this.cerrarModal();
    },

    // APIs y llamadas de datos
    listarArticulo() {
      if (!this.idAlmacen) return;

      axios
        .get(
          `/articulo/listarArticuloVenta?buscar=${this.buscarA}&idAlmacen=${this.idAlmacen}`
        )
        .then((response) => {
          this.arrayArticulo = response.data.articulos;
        })
        .catch((error) => {
          console.error("Error al listar artículos:", error);
        });
    },
    async selectAlmacen() {
      try {
        const response = await axios.get("/almacen/selectAlmacen");
        this.arrayAlmacenes = response.data.almacenes;
        this.obtenerAlmacenPredeterminado();
      } catch (error) {
        console.error("Error al obtener almacenes:", error);
      }
    },
    async obtenerAlmacenPredeterminado() {
      try {
        const response = await axios.get(
          "/api/configuracion/almacen-predeterminado"
        );
        const almacenId = response.data.almacen_predeterminado_id;
        this.selectedAlmacen = almacenId;
        this.idAlmacen = almacenId;
      } catch (error) {
        console.error("Error al obtener almacén predeterminado:", error);
      }
    },
    getAlmacenProductos(event) {
      this.idAlmacen = event.value;
    },

    // Gestión de ventas
    abrirTipoVenta() {
      this.modal2 = true;
      this.cliente = this.nombreCliente;
      this.step = 1;
    },
    seleccionarTipoVenta(tipo) {
      this.tipoVenta = tipo;
      this.tipoVentaSeleccionado = true;

      if (tipo === "contado") {
        this.idtipo_venta = 1;
      } else if (tipo === "credito") {
        this.idtipo_venta = 2;
      } else if (tipo === "adelantada") {
        this.idtipo_venta = 3;
      }
    },
    buscarVenta() {
      this.listarVenta(1, this.buscar);
    },
    listarVenta(page, buscar, criterio = "") {
      axios
        .get(`/venta?page=${page}&buscar=${buscar}&criterio=${criterio}`)
        .then((response) => {
          this.arrayVenta = response.data.ventas.data;
          this.pagination = response.data.pagination;
        })
        .catch((error) => {
          console.error("Error al listar ventas:", error);
        });
    },
    onPageChange(event) {
      let page = event.page + 1; // PrimeVue pages are 0-based
      this.listarVenta(page, this.buscar);
    },
    ocultarDetalle() {
      this.listado = 1;
    },

    // Gestión de comprobantes
    async obtenerDatosUsuario() {
      try {
        const response = await axios.get("/venta");
        this.usuarioAutenticado = response.data.usuario.usuario;
        this.idrol = response.data.usuario.idrol;
        this.puntoVentaAutenticado = response.data.codigoPuntoVenta;
      } catch (error) {
        console.error("Error al obtener datos de usuario:", error);
      }
    },
    async obtenerDatosSesionYComprobante() {
      try {
        const response = await axios.get("/obtener-ultimo-comprobante");
        this.last_comprobante = response.data.last_comprobante;
        this.nextNumber();
      } catch (error) {
        console.error("Error al obtener último comprobante:", error);
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

    // Métodos para modales
    abrirModal() {
      this.listarArticulo();
      this.modal = true;
      this.tituloModal = "Seleccione los artículos que desee";
    },
    cerrarModal() {
      this.modal = false;
    },
    cerrarModal2() {
      this.modal2 = false;
      this.reiniciarFormulario();
    },

    // Métodos para ventas a crédito
    generarCuotas() {
      if (!this.numero_cuotas || !this.tiempo_diaz) {
        Swal.fire({
          icon: "warning",
          title: "Campos incompletos",
          text: "Ingrese la cantidad de cuotas y frecuencia de pagos",
        });
        return;
      }

      this.cuotas = [];
      const fechaHoy = new Date();
      const montoEntero = Math.floor(this.calcularTotal / this.numero_cuotas);
      const montoDecimal = (
        this.calcularTotal -
        montoEntero * (this.numero_cuotas - 1)
      ).toFixed(2);
      let saldoRestante = this.calcularTotal;

      for (let i = 0; i < this.numero_cuotas; i++) {
        const fechaPago = new Date(
          fechaHoy.getTime() + (i + 1) * this.tiempo_diaz * 24 * 60 * 60 * 1000
        );

        const cuota = {
          fecha_pago: fechaPago.toISOString().split("T")[0],
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
        saldoRestante = parseFloat(saldoRestante).toFixed(2);

        this.cuotas.push(cuota);
      }
    },

    // Métodos para ventas al contado
    validarYRegistrarPago() {
      if (!this.recibido) {
        this.montoInvalido = true;
        return;
      }

      if (!this.montoValido) {
        this.montoInvalido = true;
        return;
      }

      this.registrarVenta(1); // 1 para pago en efectivo
    },

    // Método para venta adelantada
    validarYRegistrarVentaAdelantada() {
      // Validar campos obligatorios
      if (!this.direccionEntrega) {
        Swal.fire({
          icon: "warning",
          title: "Datos incompletos",
          text: "Debe ingresar la dirección de entrega",
        });
        return;
      }

      if (!this.telefonoContacto) {
        Swal.fire({
          icon: "warning",
          title: "Datos incompletos",
          text: "Debe ingresar un teléfono de contacto",
        });
        return;
      }

      if (!this.fechaEntrega) {
        Swal.fire({
          icon: "warning",
          title: "Datos incompletos",
          text: "Debe seleccionar una fecha de entrega",
        });
        return;
      }

      if (!this.tipoPagoAdelantado) {
        Swal.fire({
          icon: "warning",
          title: "Datos incompletos",
          text: "Debe seleccionar un método de pago",
        });
        return;
      }

      // Validar monto si es pago en efectivo
      if (this.tipoPagoAdelantado === "efectivo") {
        if (!this.montoAdelantado) {
          Swal.fire({
            icon: "error",
            title: "Monto inválido",
            text: "Debe ingresar el monto recibido",
          });
          return;
        }

        if (
          this.montoAdelantado <
          this.calcularTotal * parseFloat(this.monedaVenta[0])
        ) {
          Swal.fire({
            icon: "error",
            title: "Monto inválido",
            text: "El monto recibido debe ser mayor o igual al total a pagar",
          });
          return;
        }
      }

      // Determinar el tipo de pago según la selección
      let idtipo_pago = 1; // Efectivo por defecto
      if (this.tipoPagoAdelantado === "transferencia") idtipo_pago = 2;
      if (this.tipoPagoAdelantado === "tarjeta") idtipo_pago = 3;
      if (this.tipoPagoAdelantado === "qr") idtipo_pago = 4;

      console.log(
        "Registrando venta adelantada con tipo de pago:",
        idtipo_pago
      );

      // Registrar la venta
      this.registrarVenta(idtipo_pago);
    },

    // 2. Modificar computed property datosAdelantadosValidos
    // Ubicación: En el componente Vue, dentro de computed:

    datosAdelantadosValidos() {
      // Validar campos requeridos
      if (
        !this.direccionEntrega ||
        !this.telefonoContacto ||
        !this.fechaEntrega ||
        !this.tipoPagoAdelantado
      ) {
        return false;
      }

      // Si es pago en efectivo, validar monto
      if (this.tipoPagoAdelantado === "efectivo") {
        return (
          this.montoAdelantado &&
          this.montoAdelantado >=
            this.calcularTotal * parseFloat(this.monedaVenta[0])
        );
      }

      // Para otros tipos de pago
      return true;
    },
    // Método para registrar venta adelantada
    async registrarVentaAdelantada(idtipo_pago) {
      await this.buscarOCrearCliente();

      // Preparar datos de venta adelantada
      const ventaData = {
        idcliente: this.idcliente,
        tipo_comprobante: this.tipo_comprobante,
        serie_comprobante: this.serie_comprobante,
        num_comprobante: this.num_comprob,
        impuesto: this.impuesto || 0.18,
        total: this.calcularTotal,
        idAlmacen: this.idAlmacen,
        idtipo_pago: idtipo_pago,
        idtipo_venta: this.idtipo_venta,
        data: this.arrayDetalle,
        estado: "Pendiente", // Estado inicial para ventas adelantadas

        // Datos específicos de la entrega
        direccion_entrega: this.direccionEntrega,
        telefono_contacto: this.telefonoContacto,
        fecha_entrega: this.fechaEntrega
          ? this.fechaEntrega.toISOString().split("T")[0]
          : null,
        observaciones: this.observaciones,
      };

      // Agregar datos de pago
      if (this.tipoPagoAdelantado === "efectivo") {
        ventaData.monto_recibido = this.montoAdelantado;
        ventaData.cambio = parseFloat(this.calcularCambioAdelantado);
      }

      try {
        this.mostrarSpinner = true;
        const response = await axios.post("/venta/registrar", ventaData);

        if (response.data.id) {
          // Venta exitosa
          this.listado = 1;
          this.cerrarModal2();
          this.listarVenta(1, "", "num_comprob");
          this.ejecutarFlujoCompleto();

          Swal.fire(
            "Pedido registrado",
            "La venta adelantada se ha registrado correctamente y quedará en estado Pendiente hasta la entrega",
            "success"
          );

          this.reiniciarFormulario();
        } else {
          Swal.fire(
            "Error",
            "No se pudo completar la venta adelantada",
            "error"
          );
        }
      } catch (error) {
        console.error("Error al registrar venta adelantada:", error);
        Swal.fire(
          "Error",
          "Ocurrió un error al procesar la venta adelantada",
          "error"
        );
      } finally {
        this.mostrarSpinner = false;
      }
    },

    // Registro de venta
    async registrarVenta(idtipo_pago = 1) {       
    try {
        // Mostrar indicador de carga
        this.mostrarSpinner = true;         

        // Crear o buscar cliente
        await this.buscarOCrearCliente();         

        // Preparar datos comunes para cualquier tipo de venta
        const ventaData = {
            idcliente: this.idcliente,
            tipo_comprobante: this.tipo_comprobante,
            serie_comprobante: this.serie_comprobante,
            num_comprobante: this.num_comprob,
            impuesto: this.impuesto || 0.18,
            total: this.calcularTotal,
            idAlmacen: this.idAlmacen,
            idtipo_pago: idtipo_pago, // Aquí defines si es contado (1) o QR (4)
            idtipo_venta: this.idtipo_venta,
            data: this.arrayDetalle,
        };         

        // [resto de tu código de preparación de datos]

        console.log("Datos de venta a enviar:", ventaData);
        const response = await axios.post("/venta/registrar", ventaData);

        if (response.data.id) {
            // Venta exitosa
            this.listado = 1;
            this.cerrarModal2();
            this.listarVenta(1, "", "num_comprob");
            this.ejecutarFlujoCompleto();

            // Verificar y mostrar información de la caja
            if (response.data.caja) {
                this.$toast.add({
                    severity: 'info',
                    summary: 'Información de Caja',
                    detail: `
                        Ventas al Contado: ${this.formatCurrency(response.data.caja.ventasContado)}
                        Pagos QR: ${this.formatCurrency(response.data.caja.pagosQR)}
                        Saldo Actual: ${this.formatCurrency(response.data.caja.saldoCaja)}
                    `,
                    life: 5000
                });
            }

            // [resto de tu lógica de mensajes]
            if (this.tipoVenta === "credito") {
                Swal.fire(
                    "Venta exitosa",
                    "La venta a crédito se ha registrado correctamente",
                    "success"
                );
            } else if (this.tipoVenta === "adelantada") {
                Swal.fire(
                    "Pedido registrado",
                    "La venta adelantada se ha registrado correctamente y quedará en estado Pendiente hasta la entrega",
                    "success"
                );
            } else {
                this.imprimirResivo(response.data.id);
            }

            this.reiniciarFormulario();
        } else {
            // Error en la venta
            Swal.fire("Error", "No se pudo completar la venta", "error");
        }
    } catch (error) {
        console.error("Error al registrar venta:", error);
        Swal.fire(
            "Error",
            "Ocurrió un error al procesar la venta: " +
                (error.response ? error.response.data.error : error.message),
            "error"
        );
    } finally {
        this.mostrarSpinner = false;
    }
},
    // Impresión y visualización
    // En el método verVenta() de tu componente Vue
    verVenta(id) {
      this.listado = 2;
      this.cargando = true;

      // Obtener cabecera de venta
      axios
        .get(`/venta/obtenerCabecera?id=${id}`)
        .then((response) => {
          if (response.data.venta) {
            // Datos básicos
            const venta = response.data.venta;
            this.ventaDetalle = venta;
            this.cliente = venta.nombre;
            this.tipo_comprobante = venta.tipo_comprobante;
            this.num_comprobante = venta.num_comprobante;
            this.total = venta.total;

            // Si es venta a crédito, cargar info de crédito
            if (venta.idtipo_venta == 2) {
              this.creditoInfo = response.data.credito || null;
              this.cuotasCredito = response.data.cuotas || [];
            } else {
              // Limpiar datos de crédito si no es venta a crédito
              this.creditoInfo = null;
              this.cuotasCredito = [];
            }
          } else {
            // Manejar el caso de venta no encontrada
            Swal.fire(
              "Error",
              "No se pudo encontrar la información de la venta",
              "error"
            );
          }
          this.cargando = false;
        })
        .catch((error) => {
          console.error("Error al obtener cabecera:", error);
          Swal.fire(
            "Error",
            "No se pudo obtener los detalles de la venta",
            "error"
          );
          this.cargando = false;
        });

      // Obtener detalles de productos
      axios
        .get(`/venta/obtenerDetalles?id=${id}`)
        .then((response) => {
          this.arrayDetalle = response.data.detalles;
        })
        .catch((error) => {
          console.error("Error al obtener detalles:", error);
        });
    },

    // Formato para fechas
    formatFecha(fecha) {
      if (!fecha) return "N/A";

      const date = new Date(fecha);
      return date.toLocaleDateString("es-ES", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    },

    // Formato para montos con moneda
    formatCurrency(amount) {
      if (amount === null || amount === undefined) return "N/A";

      const valor = parseFloat(amount) * parseFloat(this.monedaVenta[0] || 1);
      return valor.toFixed(2) + " " + (this.monedaVenta[1] || "Bs");
    },

    // Obtener el nombre del tipo de pago
    obtenerNombrePago(idTipoPago) {
      const tipos = {
        1: "Efectivo",
        2: "Transferencia",
        3: "Tarjeta",
        4: "QR",
      };

      return tipos[idTipoPago] || "Desconocido";
    },

    // Determinar color según estado
    getEstadoSeverity(estado) {
      switch (estado) {
        case "Pendiente":
          return "warning";
        case "Entregado":
          return "success";
        case "Registrado":
          return "info";
        case "Anulado":
          return "danger";
        default:
          return "info";
      }
    },

    // Ver el plan de pagos completo (para ventas a crédito)
    verPlanPagos(id) {
      // Si no tiene información de crédito, intente obtenerla
      if (!this.creditoInfo || !this.cuotasCredito.length) {
        axios
          .get(`/venta/obtenerCuotas?id=${id}`)
          .then((response) => {
            if (response.data.cuotas && response.data.cuotas.length > 0) {
              this.cuotasCredito = response.data.cuotas;

              if (response.data.credito) {
                this.creditoInfo = response.data.credito;
              }

              // Mostrar modal o diálogo con el plan de pagos completo
              this.mostrarDetalleCredito = true;
            } else {
              Swal.fire(
                "Info",
                "No se encontró información de cuotas para esta venta",
                "info"
              );
            }
          })
          .catch((error) => {
            console.error("Error al obtener plan de pagos:", error);
            Swal.fire("Error", "No se pudo obtener el plan de pagos", "error");
          });
      } else {
        // Ya tiene la información, solo mostrar el modal o diálogo
        this.mostrarDetalleCredito = true;
      }
    },

    // Método para volver al listado desde la vista de detalle
    ocultarDetalle() {
      this.listado = 1;
      // Limpiar datos de detalle
      this.ventaDetalle = null;
      this.cliente = "";
      this.tipo_comprobante = "";
      this.num_comprobante = "";
      this.total = 0;
      this.creditoInfo = null;
      this.cuotasCredito = [];
      this.arrayDetalle = [];
    },

    // Obtener información del crédito
    obtenerInfoCredito(id) {
      axios
        .get(`/venta/obtenerCuotas?id=${id}`)
        .then((response) => {
          if (response.data.credito) {
            this.infoCredito = response.data.credito;
          }
        })
        .catch((error) => {
          console.log("No hay crédito asociado a esta venta:", error.message);
        });
    },

    // Obtener nombre del método de pago
    obtenerMetodoPago(idTipoPago) {
      const metodos = {
        1: "Efectivo",
        2: "Transferencia",
        3: "Tarjeta",
        4: "QR",
      };

      this.metodoPago = metodos[idTipoPago] || "Desconocido";
    },

    // Formatear fechas
    formatDate(dateString) {
      if (!dateString) return "N/A";

      const date = new Date(dateString);
      return date.toLocaleDateString("es-ES");
    },

    // Formatear montos
    formatCurrency(amount) {
      if (amount === null || amount === undefined) return "N/A";

      return (
        (amount * parseFloat(this.monedaVenta[0])).toFixed(2) +
        " " +
        this.monedaVenta[1]
      );
    },

    // Determinar color de estado
    getEstadoSeverity(estado) {
      switch (estado) {
        case "Pendiente":
          return "warning";
        case "Entregado":
          return "success";
        case "Registrado":
          return "info";
        case "Anulado":
          return "danger";
        default:
          return "info";
      }
    },
    imprimirResivo(id) {
      Swal.fire({
        title: "Selecciona un tamaño para imprimir el recibo",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "CARTA",
        cancelButtonText: "ROLLO",
        reverseButtons: true,
      }).then((result) => {
        const endpoint = result.value ? "imprimirCarta" : "imprimirRollo";
        const filename = result.value ? "recibo_carta.pdf" : "recibo_rollo.pdf";

        axios
          .get(`/resivo/${endpoint}/${id}`, { responseType: "blob" })
          .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          })
          .catch((error) => {
            console.error("Error al imprimir recibo:", error);
          });
      });
    },
    desactivarVenta(id) {
      Swal.fire({
        title: "¿Está seguro de anular esta venta?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Aceptar",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
      }).then((result) => {
        if (result.value) {
          axios
            .put("/venta/desactivar", { id })
            .then(() => {
              this.listarVenta(1, "", "num_comprobante");
              Swal.fire(
                "Anulado",
                "La venta ha sido anulada con éxito",
                "success"
              );
            })
            .catch((error) => {
              console.error("Error al anular venta:", error);
            });
        }
      });
    },

    // Utilidades
    datosConfiguracion() {
      axios
        .get("/configuracion")
        .then((response) => {
          const config = response.data.configuracionTrabajo;
          this.saldosNegativos = config.saldosNegativos;
          this.monedaVenta = [
            config.valor_moneda_venta,
            config.simbolo_moneda_venta,
          ];
        })
        .catch((error) => {
          console.error("Error al obtener configuración:", error);
        });
    },
    reiniciarFormulario() {
      this.idcliente = 0;
      this.documento = "";
      this.nombreCliente = "";
      this.tipo_comprobante = "RESIVO";
      this.tipo_documento = "1";
      this.serie_comprobante = "";
      this.num_comprob = "";
      this.arrayDetalle = [];
      this.arraySeleccionado = null;
      this.selectedArticulo = null;
      this.codigo = "";
      this.cantidad = 1;
      this.recibido = null;
      this.tipoVenta = "";
      this.tipoVentaSeleccionado = false;
      this.step = 1;
      this.cuotas = [];

      // Resetear campos de venta adelantada
      this.direccionEntrega = "";
      this.telefonoContacto = "";
      this.fechaEntrega = null;
      this.observaciones = "";
      this.tipoPagoAdelantado = null;
      this.montoAdelantado = null;
    },
  },
  mounted() {
    this.datosConfiguracion();
    this.selectAlmacen();
    this.listarVenta(1, this.buscar);
    this.ejecutarFlujoCompleto();
  },
};
</script>

<style scoped>
/* Estilos para la navegación por pasos */
.step-indicators {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  position: relative;
  padding: 0 10px;
}

.step-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 2;
}

.step {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #ccc;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-bottom: 5px;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.step.active {
  background-color: #007bff;
  color: white;
  border-color: #0056b3;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

.step.completed {
  background-color: #28a745;
  color: white;
  border-color: #1e7e34;
}

.step-label {
  font-size: 12px;
  font-weight: bold;
  text-align: center;
}

.step-line {
  height: 3px;
  background-color: #ccc;
  flex-grow: 1;
  z-index: 1;
  margin: 0 5px;
  position: relative;
  top: -20px;
  transition: background-color 0.3s ease;
}

.step-line-active {
  background-color: #28a745;
  animation: progressLine 0.5s ease-in-out;
}

@keyframes progressLine {
  0% {
    width: 0%;
    opacity: 0;
  }
  100% {
    width: 100%;
    opacity: 1;
  }
}

/* Estilos para el formulario */
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

.footer {
  padding: 1rem;
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

/* Estilos para el detalle de venta */
.encabezado-venta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-radius: 5px;
  margin-bottom: 20px;
}

.bg-green-light {
  background-color: rgba(80, 200, 120, 0.15);
  border-left: 4px solid #50c878;
}

.bg-yellow-light {
  background-color: rgba(255, 193, 7, 0.15);
  border-left: 4px solid #ffc107;
}

.bg-blue-light {
  background-color: rgba(33, 150, 243, 0.15);
  border-left: 4px solid #2196f3;
}

.tipo-venta {
  display: flex;
  align-items: center;
  font-weight: bold;
  font-size: 1.1rem;
}

.tipo-venta i {
  font-size: 1.4rem;
  margin-right: 8px;
}

.datos-principales,
.datos-adelantada-grid,
.datos-contado-grid,
.plan-pagos-header {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.datos-principales {
  margin-bottom: 24px;
  padding: 16px;
  background-color: #f8f9fa;
  border-radius: 5px;
}

.datos-item {
  margin-bottom: 12px;
}

.datos-item label {
  display: block;
  font-weight: bold;
  color: #666;
  margin-bottom: 4px;
  font-size: 0.9rem;
}

.datos-item div {
  font-size: 1rem;
}

.datos-especificos {
  margin-bottom: 24px;
  padding: 16px;
  border-radius: 5px;
  border: 1px solid #e0e0e0;
}

.datos-especificos h4 {
  margin-top: 0;
  margin-bottom: 16px;
  font-size: 1.1rem;
  color: #333;
  display: flex;
  align-items: center;
}

.datos-especificos h4 i {
  margin-right: 8px;
  font-size: 1.2rem;
}

.datos-adelantada-grid .full-width {
  grid-column: 1 / -1;
}

.plan-pagos-header {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #eee;
}

.plan-pagos-table {
  margin-bottom: 16px;
}

.productos-titulo {
  display: flex;
  align-items: center;
  margin: 24px 0 16px;
  font-size: 1.1rem;
  color: #333;
}

.productos-titulo i {
  margin-right: 8px;
  font-size: 1.2rem;
}

.productos-tabla {
  margin-bottom: 24px;
}

.total-section {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 12px 16px;
  background-color: #f8f9fa;
  border-radius: 5px;
  margin-bottom: 24px;
}

.total-label {
  font-weight: bold;
  font-size: 1.1rem;
  margin-right: 16px;
}

.total-amount {
  font-weight: bold;
  font-size: 1.2rem;
  color: #2196f3;
}

.botones-accion {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.no-data {
  padding: 16px;
  text-align: center;
  color: #666;
  font-style: italic;
  background-color: #f8f9fa;
  border-radius: 5px;
}

/* Estilos para el modal de detalle del producto */
.product-image-container {
  text-align: center;
  margin-bottom: 1rem;
  border: 1px solid #eee;
  padding: 10px;
  border-radius: 5px;
  background-color: white;
}

.product-image {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
}

.product-price-section,
.product-info {
  background-color: #f8f9fa;
  border-radius: 5px;
  padding: 15px;
  height: 100%;
  border: 1px solid #e9ecef;
}

.price-title,
.info-title {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 15px;
  color: #495057;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 8px;
}

.product-price {
  margin-bottom: 20px;
}

.precio-opcion {
  margin-bottom: 8px;
  display: flex;
  align-items: center;
}

.precio-opcion input {
  margin-right: 8px;
}

.precio-opcion label {
  margin-bottom: 0;
  cursor: pointer;
}

.selected-price {
  color: #2196f3;
  font-size: 1.5rem;
  margin: 1rem 0;
  font-weight: bold;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  padding: 8px;
  background-color: white;
  border-radius: 4px;
  border-left: 3px solid #2196f3;
}

.detail-label {
  font-weight: bold;
  color: #495057;
}

.detail-value {
  text-align: right;
  color: #212529;
}

.purchase-options {
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 5px;
  margin-top: 1rem;
  border: 1px solid #e9ecef;
}

.alert {
  margin-top: 15px;
  border-radius: 5px;
  padding: 10px 15px;
  font-size: 0.9rem;
}

.alert-success {
  background-color: #d4edda;
  border-color: #c3e6cb;
  color: #155724;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffeeba;
  color: #856404;
}

.alert-danger {
  background-color: #f8d7da;
  border-color: #f5c6cb;
  color: #721c24;
}

/* Utilidades */
.mr-2 {
  margin-right: 8px;
}

.mb-3 {
  margin-bottom: 15px;
}

/* Media queries */
@media (min-width: 768px) {
  .form-row {
    flex-wrap: nowrap;
  }
  .form-group {
    flex: 1;
  }
}

@media (max-width: 768px) {
  .detail-row {
    flex-direction: column;
  }

  .detail-value {
    text-align: left;
    margin-top: 4px;
  }
}
/* Estilos para el modal de detalle de producto */
.product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 8px;
  border-bottom: 2px solid #f0f0f0;
}

.product-code {
  font-size: 0.9rem;
  color: #666;
  padding: 4px 10px;
  background-color: #f5f5f5;
  border-radius: 4px;
}

.product-details-container {
  margin: 15px 0;
}

.product-image-container {
  text-align: center;
  margin-bottom: 1.5rem;
  border: 1px solid #eee;
  padding: 15px;
  border-radius: 8px;
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-image {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
}

.stock-indicator {
  margin-top: 15px;
  border-radius: 8px;
  padding: 12px 15px;
  font-size: 1rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stock-title {
  display: flex;
  align-items: center;
  font-weight: bold;
  margin-bottom: 5px;
}

.stock-value {
  font-size: 1.2rem;
  font-weight: bold;
  text-align: center;
}

.product-description {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e9ecef;
  margin-bottom: 1.5rem;
}

.description-content {
  padding: 10px;
  background-color: white;
  border-radius: 6px;
  min-height: 100px;
  max-height: 200px;
  overflow-y: auto;
  font-size: 0.95rem;
  line-height: 1.5;
}

.product-price-section,
.product-info {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  height: 100%;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.info-section-title {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 15px;
  color: #495057;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 8px;
  display: flex;
  align-items: center;
}

.precio-opcion {
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  background-color: white;
  padding: 8px 12px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.precio-opcion:hover {
  background-color: #f0f8ff;
  transform: translateX(3px);
}

.precio-opcion input {
  margin-right: 10px;
}

.precio-opcion label {
  margin-bottom: 0;
  cursor: pointer;
  width: 100%;
  display: flex;
  justify-content: space-between;
}

.price-selected-label {
  margin-top: 20px;
  margin-bottom: 8px;
  color: #495057;
}

.selected-price {
  color: #2196f3;
  font-size: 1.8rem;
  margin: 0.5rem 0 1.5rem;
  font-weight: bold;
  text-align: center;
  background-color: #e6f4ff;
  padding: 10px;
  border-radius: 6px;
}

.price-not-selected {
  color: #dc3545;
  background-color: #fff5f5;
  font-size: 1.2rem;
}

.purchase-options {
  margin-top: 1.5rem;
  background-color: white;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.purchase-label {
  display: block;
  font-weight: bold;
  margin-bottom: 8px;
  color: #495057;
}

.quantity-control {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  padding: 10px;
  background-color: white;
  border-radius: 6px;
  border-left: 3px solid #2196f3;
  transition: all 0.2s ease;
}

.detail-row:hover {
  background-color: #f0f8ff;
  transform: translateX(3px);
}

.detail-label {
  font-weight: bold;
  color: #495057;
}

.detail-value {
  text-align: right;
  color: #212529;
  font-weight: 500;
}

.additional-info {
  margin-top: 20px;
}

.additional-info-content {
  padding: 10px;
  background-color: white;
  border-radius: 6px;
  min-height: 80px;
  max-height: 150px;
  overflow-y: auto;
}

.footer-actions {
  display: flex;
  justify-content: flex-end;
}

.mr-2 {
  margin-right: 0.5rem;
}

.mt-4 {
  margin-top: 1rem;
}
/* Estilos para el contenedor principal */
.product-details-container {
  margin: 15px 0;
  display: flex;
  flex-wrap: wrap;
}

/* Estilos para la imagen del producto */
.product-image-container {
  text-align: center;
  margin-bottom: 1rem;
  border: 1px solid #eee;
  padding: 15px;
  border-radius: 8px;
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-image {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
}

/* Estilos para el indicador de stock */
.stock-indicator {
  margin: 0.8rem 0;
  border-radius: 8px;
  padding: 10px 15px;
  font-size: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stock-title {
  display: flex;
  align-items: center;
  font-weight: bold;
  margin-bottom: 5px;
}

.stock-value {
  font-size: 1.2rem;
  font-weight: bold;
  text-align: center;
}

/* Estilos para la fila de descripción y opciones */
.product-info-row {
  display: flex;
  margin-top: 1rem;
  flex-wrap: wrap;
}

/* Estilos para la descripción */

/* Estilos para las opciones de compra */
.purchase-controls {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e9ecef;
  height: 100%;
  min-height: 200px;
}

.option-content {
  padding: 10px;
  background-color: white;
  border-radius: 6px;
  height: calc(100% - 45px);
}

.purchase-label {
  display: block;
  font-weight: bold;
  margin-bottom: 8px;
  margin-top: 10px;
  color: #495057;
}

.quantity-control {
  margin-top: 5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Estilos para la sección de precios */
.product-price-section {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  height: 100%;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.precio-opcion {
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  background-color: white;
  padding: 10px 12px;
  border-radius: 6px;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.precio-opcion:hover {
  background-color: #f0f8ff;
  transform: translateX(3px);
}

.precio-opcion input {
  margin-right: 10px;
}

.precio-opcion label {
  margin-bottom: 0;
  cursor: pointer;
  width: 100%;
  display: flex;
  justify-content: space-between;
}

/* Estilos para el precio seleccionado */
.precio-seleccionado-container {
  margin-top: 20px;
}

.price-selected-label {
  margin-bottom: 8px;
  color: #495057;
  font-weight: bold;
}

.selected-price {
  color: #2196f3;
  font-size: 1.8rem;
  margin: 0.5rem 0;
  font-weight: bold;
  text-align: center;
  background-color: #e6f4ff;
  padding: 10px;
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.price-not-selected {
  color: #dc3545;
  background-color: #fff5f5;
  font-size: 1.2rem;
}

/* Estilos para la información del producto */
.product-info {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  height: 100%;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  padding: 8px 12px;
  background-color: white;
  border-radius: 6px;
  border-left: 3px solid #2196f3;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.detail-row:hover {
  background-color: #f0f8ff;
  transform: translateX(3px);
}

.detail-label {
  font-weight: bold;
  color: #495057;
}

.detail-value {
  text-align: right;
  color: #212529;
  font-weight: 500;
}

/* Estilos para los títulos de sección */
.info-section-title {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 15px;
  color: #495057;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 8px;
  display: flex;
  align-items: center;
}

/* Utilidades */
.mr-2 {
  margin-right: 0.5rem;
}
/* Estructura general del layout */
.product-layout {
  display: flex;
  flex-direction: column;
}

.product-details-container {
  margin: 15px 0;
  display: flex;
  flex-wrap: wrap;
}

/* Estilos para la imagen del producto */
.product-image-container {
  text-align: center;
  margin-bottom: 1rem;
  padding: 15px;
  border-radius: 8px;
  background-color: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #eee;
}

.product-image {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
}

/* Estilos para el indicador de stock */
.stock-indicator {
  margin: 0.8rem 0;
  border-radius: 8px;
  padding: 12px 15px;
  font-size: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stock-title {
  display: flex;
  align-items: center;
  font-weight: bold;
  margin-bottom: 5px;
}

.stock-value {
  font-size: 1.2rem;
  font-weight: bold;
  text-align: center;
}

.single-line-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.25rem 0.5rem;
  gap: 0.5rem;
}

.price-display {
  display: flex;
  align-items: center;
  white-space: nowrap;
}

.p-dropdown,
.p-inputnumber {
  margin: 0;
}

.action-buttons {
  display: flex;
  align-items: center;
}

/* Ensure everything is extra compact */
:deep(.p-dropdown),
:deep(.p-inputnumber) {
  height: 2rem;
}

:deep(.p-dropdown .p-dropdown-label) {
  padding: 0.25rem 0.5rem;
}

:deep(.p-inputnumber .p-inputtext) {
  padding: 0.25rem 0.5rem;
  width: 3rem;
}

:deep(.p-button-sm) {
  padding: 0.25rem 0.5rem;
}

</style>