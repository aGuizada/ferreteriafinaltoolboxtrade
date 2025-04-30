import useVuelidate from "@vuelidate/core";
import { required, minValue } from "@vuelidate/validators";
import Panel from "primevue/panel";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ColumnGroup from "primevue/columngroup";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Dialog from "primevue/dialog";
import Dropdown from "primevue/dropdown";
import Toast from 'primevue/toast';

export default {
  setup() {
    return {
      v$: useVuelidate(),
    };
  },

  data() {
    return {
      submitted: false,
      array_ingresos: [],
      array_cuotas: [],
      array_detalles_ingreso: [],
      displayListaCuotas: false,
      displayPagarCuota: false,
      displayMostrarDetalles: false,
      lista_tipo_pago_cuotas: [
        { nombre: "Efectivo" },
        { nombre: "Tarjeta" },
        { nombre: "QR" },
      ],
      idCuotaActual: null,
      form: {
        fecha_pago: null,
        tipo_pago_cuota: null,
        cuota_actual: null,
        pago_actual: null,
      },
      cabecera: {
        nombre_proveedor: null,
        tipo_comprobante: null,
        serie_comprobante: null,
        num_comprobante: null,
        fecha_hora: null,
        total: null,
        cuota_inicial: null,
        tipo_pago_inicial: null,
        nombre_almacen: null,
        descuento_global: null,
      },
    };
  },

  validations() {
    return {
      form: {
        fecha_pago: {
          required
        },
        tipo_pago_cuota: {
          required
        },
        cuota_actual: {
          required
        },
        pago_actual: {
          required,
          minValueValue: minValue(1),
        },
      }
    }
  },

  components: {
    Panel,
    DataTable,
    Column,
    ColumnGroup,
    InputText,
    Button,
    Tag,
    Dialog,
    InputNumber,
    Dropdown,
    Toast,
  },

  computed: {},

  watch: {},

  methods: {

    listarIngresosCuotas() {
      let me = this;
      var url = "/ingresoCuotas/listarIngresos";
      axios
        .get(url)
        .then(function (response) {
          var respuesta = response.data;
          me.array_ingresos = respuesta.ingresos;
        })
        .catch(function (error) {
          console.log(error);
        });
    },

    async listarCuotasPorIngreso(ingresoId) {
      try {
        const cuotasResponse = await axios.get(
          "/ingresoCuotas/cuotasPorIngreso?id=" + ingresoId,
        );
    
        if (cuotasResponse.data.status === "success") {
          this.array_cuotas = cuotasResponse.data.cuotas.map((cuota, index) => ({
            ...cuota,
            id: cuota.id,
            fecha_pago: cuota.fecha_pago.split(" ")[0],
            precio_cuota: parseFloat(cuota.precio_cuota).toFixed(2),
            total_cancelado: parseFloat(cuota.total_cancelado).toFixed(2),
            saldo_restante: parseFloat(cuota.saldo_restante).toFixed(2),
            fecha_cancelado: cuota.fecha_cancelado ? cuota.fecha_cancelado.split(" ")[0] : null,
            estado: cuota.estado === 'Cuota Inicial' ? 'Pagado' : cuota.estado,
            tipo_pago_cuota: cuota.tipo_pago_cuota || (cuota.estado === 'Cuota Inicial' ? this.getTipoPagoInicial(ingresoId) : null),
            enumeracion: index + 1
          }));
        }
      } catch (error) {
        console.error(error);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Error al cargar las cuotas',
          life: 3000
        });
      }
    },

    getTipoPagoInicial(ingresoId) {
      const ingreso = this.array_ingresos.find(i => i.id === ingresoId);
      return ingreso ? ingreso.tipo_pago_inicial : 'Efectivo';
    },
    
    cortarString(cadena) {
      return cadena.substring(0, 9);
    },

    openModalListaCuotas(ingresoId) {
      this.displayListaCuotas = true;
      this.listarCuotasPorIngreso(ingresoId);
    },

    closeModalListaCuotas() {
      this.displayListaCuotas = false;
      this.array_cuotas = [];
    },

    openModalPagoCuota(data) {
      this.displayListaCuotas = false;
      this.displayPagarCuota = true;
      this.form.cuota_actual = Number(data.precio_cuota);
      this.idCuotaActual = data.id;

      const fechaActual = new Date();
      this.form.fecha_pago = fechaActual.toISOString().split("T")[0];
    },

    closeModalPagoCuota() {
      this.displayPagarCuota = false;
    },

    validarFormPagoCuota() {
      return this.v$.form.$invalid;
    },

    async pagarCuota() {

      this.submitted = true;

      if (this.validarFormPagoCuota()) {
        return;
      }

      if (this.form.cuota_actual != this.form.pago_actual) {
        this.$toast.add({
            severity: "error",
            summary: "Error",
            detail: "Monto a pagar debe ser igual al Cuota actual",
            life: 3000,
          });
        return;
      }

      try {
        const response = await axios.post("/ingresoCuotas/pagarCuota", {
          id: this.idCuotaActual,
          form: this.form,
        });
        if (response.data.status === "success") {
          this.$toast.add({
            severity: "success",
            summary: "Exito",
            detail: "Cuota pagada correctamente",
            life: 3000,
          });
          this.closeModalPagoCuota();
          this.listarIngresosCuotas();
        } 
      } catch (error) {
        console.log(error);
        this.$toast.add({
          severity: "error",
          summary: "Error",
          detail: "Ocurrio un error al procesar el pago",
          life: 3000,
        });
      }
    },

    cancelarPagoCuota() {
      this.submitted = false;

      this.displayListaCuotas = false;
      this.displayPagarCuota = false;
      this.form.cuota_actual = null;
      this.form.fecha_pago = null;
      this.form.pago_actual = null;
      this.form.tipo_pago_cuota = null;

      this.idCuotaActual = null;
    },

    async listarDetallesIngreso(data) {
      try {
        const response = await axios.get(
          "/ingresoCuotas/listarDetallesIngreso?id=" + data.id
        );

        if (response.data.status === "success") {
          let ingreso = response.data.datos.ingreso;
          this.cabecera = {
            nombre_proveedor: data.proveedor,
            tipo_comprobante: ingreso.tipo_comprobante,
            serie_comprobante: ingreso.serie_comprobante,
            num_comprobante: ingreso.num_comprobante,
            fecha_hora: ingreso.fecha_hora,
            total: ingreso.total,
            cuota_inicial: ingreso.cuota_inicial,
            tipo_pago_inicial: ingreso.tipo_pago_cuota,
            nombre_almacen: data.nombre_almacen,
            descuento_global: ingreso.descuento_global,
          },

          this.array_detalles_ingreso = response.data.datos.articulos;

          this.displayMostrarDetalles = true;

          console.log('cabecera',this.cabecera);
        }

      } catch (error) {
        console.log(error);
        this.$toast.add({
          severity: "error",
          summary: "Error",
          detail: "Ocurrio un error al recuperar los datos",
          life: 3000,
        });
      }
    },

    closeModalMostrarDetalle() {
      this.cabecera = {
        nombre_proveedor: null,
        tipo_comprobante: null,
        serie_comprobante: null,
        num_comprobante: null,
        fecha_hora: null,
        total: null,
        cuota_inicial: null,
        tipo_pago_inicial: null,
        nombre_almacen: null,
        descuento_global: null,
      },
      this.array_detalles_ingreso = [];
      this.displayMostrarDetalles = false;
    },

    getDialogPosition() {
      console.log('direccion',window.innerWidth <= 768 ? 'right' : 'center')
      return window.innerWidth <= 768 ? 'right' : 'center';
    },

    getDialogStyle(desktopWidth = '55vw') {
      if (window.innerWidth <= 768) {
          console.log('style',window.innerWidth <= 768)
          return { width: '100vw', height: '100vh' };
      } else if (window.innerWidth <= 1366) {
          console.log('style',window.innerWidth <= 1366)
          return { width: '80vw' };
      } else {
          console.log('style',desktopWidth)
          return { width: desktopWidth };
      }
    },
  },

  created() {},

  mounted() {
    this.listarIngresosCuotas();
  },
};
