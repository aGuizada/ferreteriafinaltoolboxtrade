<template>
    <main class="main">
        <Panel>
            <Toast :breakpoints="{ '920px': { width: '100%', right: '0', left: '0' } }" style="padding-top: 40px;">
                </Toast>
                <template #header>
                    <div class="panel-header">
                        
                        <h4 class="panel-icon">Clientes</h4>
                    </div>
                </template>
            <template>
                <div class="p-grid p-mb-3">

                    <div class="p-col-12">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <Button label="Registrar Persona" icon="pi pi-user-plus" class="p-button-secondary"
                                    @click="abrirModal('persona', 'registrar')" />
                                    <input type="text" 
                                        v-model="buscar" 
                                        @keyup="listarPersona(1, buscar, 'nombre')" 
                                        class="form-control" 
                                        placeholder="Buscar cliente..." />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <DataTable :value="arrayPersona" class="p-datatable-gridlines p-datatable-sm" :paginator="true"
                    :rows="pagination.per_page" :totalRecords="pagination.total" :lazy="true"
                    :rowsPerPageOptions="[5, 10, 20]" @page="onPageChange"
                    :first="(pagination.current_page - 1) * pagination.per_page" responsiveLayout="scroll">
                    <Column header="Acciones">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-warning p-button-sm p-mr-2"
                                @click="abrirModal('persona', 'actualizar', slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-danger p-button-sm"
                                @click="eliminarCliente(slotProps.data)" />
                        </template>
                    </Column>
                    <Column field="nombre" header="Nombres"></Column>
                    <Column field="tipo_documento" header="Tipo Documento">
                        <template #body="slotProps">
                            {{ getTipoDocumentoText(slotProps.data.tipo_documento) }}
                        </template>
                    </Column>
                    <Column field="num_documento" header="Número de documento"></Column>
                </DataTable>
            </template>
        </Panel>

        <!-- Modal para agregar/actualizar -->
        <Dialog :visible.sync="modal" :containerStyle="{ width: '800px' }" :modal="true">
            <template #header>
                <h3>{{ tituloModal }}</h3>
            </template>

            <form @submit.prevent="enviarFormulario">
                <div class="p-fluid p-formgrid p-grid">
                    <div class="p-field p-col-12">
                        <label for="nombre">Nombre del cliente <span class="p-error">*</span></label>
                        <InputText id="nombre" v-model="datosFormulario.nombre" :class="{ 'p-invalid': errores.nombre }"
                            @input="validarCampo('nombre')" />
                        <small class="p-error" v-if="errores.nombre">{{ errores.nombre }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-6">
                        <label for="tipo_documento">Tipo de documento <span class="p-error">*</span></label>
                        <Dropdown id="tipo_documento" v-model="datosFormulario.tipo_documento"
                            :options="tipoDocumentoOptions" optionLabel="label" optionValue="value"
                            placeholder="Selecciona un tipo de documento"
                            :class="{ 'p-invalid': errores.tipo_documento }" @change="validarCampo('tipo_documento')" />
                        <small class="p-error" v-if="errores.tipo_documento">{{ errores.tipo_documento }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-3">
                        <label :for="'num_documento_' + datosFormulario.tipo_documento">N° {{ getTipoDocumentoLabel() }}
                            <span class="p-error">*</span></label>
                        <InputText :id="'num_documento_' + datosFormulario.tipo_documento"
                            v-model="datosFormulario.num_documento" :class="{ 'p-invalid': errores.num_documento }"
                            @input="validarCampo('num_documento')" />
                        <small class="p-error" v-if="errores.num_documento">{{ errores.num_documento }}</small>
                    </div>

                    <div class="p-field p-col-12 p-md-3">
                        <label for="complemento">Complemento</label>
                        <div class="p-inputgroup">
                            <InputText id="complemento" v-model="datosFormulario.complemento"
                                :disabled="!mostrarComplemento" />
                            <Button icon="pi pi-check" @click="mostrarComplemento = !mostrarComplemento" />
                        </div>
                    </div>
                </div>

                <div v-if="tipoAccion == 2" class="p-mb-3">
                    <Message severity="info">Este cliente fue creado por: <strong>{{ usuarioSeleccionado }}</strong>.
                    </Message>
                </div>

                <div v-if="activaredit && rolUsuario == 1" class="p-field">
                    <label for="user">Usuario <span class="p-error">*</span></label>
                    <AutoComplete id="user" v-model="usuarioSeleccionado" :suggestions="arrayUsuarioV"
                        @complete="selectUsuario" field="nombre" @item-select="getDatosUsuario"
                        placeholder="Buscar Usuario..." />
                </div>
            </form>

            <template #footer>
                <Button label="Cerrar" icon="pi pi-times" class="p-button-danger" @click="cerrarModal" />
                <Button v-if="tipoAccion == 1" label="Guardar" icon="pi pi-check" class="p-button-success"
                    @click="enviarFormulario" />
                <Button v-if="tipoAccion == 2" label="Actualizar" icon="pi pi-check"
                    class="p-button-success" @click="enviarFormulario" />
            </template>
        </Dialog>

        <div v-if="modalImportar">
            <ImportarExcelCliente @cerrar="cerrarModalImportar"></ImportarExcelCliente>
        </div>
    </main>
</template>

<script>
import AutoComplete from 'primevue/autocomplete';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Paginator from 'primevue/paginator';
import Swal from 'sweetalert2';
import { esquemaCliente } from '../constants/validations';
import Panel from 'primevue/panel';
import Toast from 'primevue/toast';
export default {
    components: {
        Card,
        Button,
        DataTable,
        Column,
        InputText,
        Dropdown,
        AutoComplete,
        Dialog,
        Paginator,
        Message,
        Panel,
        Toast,
    },
    data() {
        return {
            showDeleteConfirm: false,
            clienteAEliminar: null,
            tipoDocumentoOptions: [
                { label: 'CI - CEDULA DE IDENTIDAD', value: '1' },
                { label: 'CEX - CEDULA DE IDENTIDAD DE EXTRANJERO', value: '2' },
                { label: 'NIT - NÚMERO DE IDENTIFICACIÓN TRIBUTARIA', value: '5' },
                { label: 'PAS - PASAPORTE', value: '3' },
                { label: 'OD - OTRO DOCUMENTO DE IDENTIDAD', value: '4' }
            ],
            criterioOptions: [
                { label: 'Nombre', value: 'nombre' },
                { label: 'Documento', value: 'num_documento' },
                { label: 'Email', value: 'email' },
                { label: 'Teléfono', value: 'telefono' }
            ],
            datosFormulario: {
                nombre: '',
                tipo_documento: '',
                num_documento: '',
                complemento: '',
            },
            errores: {},
            rolUsuario: '',
            arrayUsuarioV: [],
            usuarioSeleccionado: '',
            idusuario: '',
            arrayDetalleUsuario: [],
            activaredit: false,
            arrayUsuarioFiltro: [],
            usuarioSeleccionadodos: '',
            usuariodos_id: '',
            role_id: '',
            mostrarComplemento: false,
            modalImportar: false,
            arrayPersona: [],
            modal: false,
            tituloModal: '',
            tipoAccion: 0,
            errorPersona: 0,
            errorMostrarMsjPersona: [],
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 10,
                last_page: 0,
                from: 0,
                to: 0,
            },
            criterio: 'nombre',
            buscar: '',
            usuariodos_id: '',
        }
    },
    methods: {
       // Método para realizar la búsqueda y obtener los resultados
    listarPersona(page, buscar, criterio) {
        if (/^\d+$/.test(buscar)) {
            criterio = 'ci'; // Si es un número, lo tratamos como CI
        }

        const url = `/cliente?page=${page}&buscar=${buscar}&criterio=${criterio}&usuarioid=${this.usuariodos_id}`;

        axios.get(url)
            .then(response => {
                this.arrayPersona = response.data.usuarios.data;
                this.pagination = response.data.pagination;
            })
            .catch(error => {
                console.error('Error fetching clients:', error);
            });

    },

        
        confirmarEliminarCliente(cliente) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "¿Desea eliminar este cliente?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            this.eliminarCliente(cliente);
        }
    });
},

eliminarCliente(cliente) {
    if (cliente && cliente.id) {
        axios.delete(`/cliente/eliminar/${cliente.id}`)
            .then(response => {
                // Eliminar el cliente del array local
                this.arrayPersona = this.arrayPersona.filter(c => c.id !== cliente.id);

                // Actualizar la paginación si es necesario
                if (this.arrayPersona.length === 0 && this.pagination.current_page > 1) {
                    this.listarPersona(this.pagination.current_page - 1, this.buscar, this.criterio);
                } else {
                    // Ajustar la paginación
                    this.pagination.total -= 1;
                }

                Swal.fire(
                    'Eliminado',
                    'El cliente ha sido eliminado.',
                    'success'
                );
            })
            .catch(error => {
                console.error('Error al eliminar el cliente:', error);
                if (error.response && error.response.data && error.response.data.tipo === 'integridad') {
                    Swal.fire(
                        'No se puede eliminar',
                        error.response.data.error,
                        'warning'
                    );
                } else {
                    Swal.fire(
                        'Error',
                        'No se pudo eliminar el cliente.',
                        'error'
                    );
                }
            });
    } else {
        console.error('Cliente object or ID is undefined');
    }
},
        onPageChange(event) {
            const page = event.page + 1;  // PrimeVue uses 0-based indexing
            this.listarPersona(page, this.buscar, this.criterio);
        },

        getTipoDocumentoLabel() {
            switch (this.datosFormulario.tipo_documento) {
                case '1': return 'CI';
                case '2': return 'CEX';
                case '3': return 'Pasaporte';
                case '5': return 'NIT';
                default: return 'Documento';
            }
        },

        async validarCampo(campo) {
            try {
                await esquemaCliente.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
        },
        async enviarFormulario() {
            try {
                await esquemaCliente.validate(this.datosFormulario, { abortEarly: false });
                if (this.tipoAccion == 1) {
                    this.registrarPersona(this.datosFormulario);
                } else {
                    this.datosFormulario.usuariodos_id = this.idusuario;
                    this.actualizarPersona(this.datosFormulario);
                }
                this.mostrarComplemento = false;
            } catch (error) {
                const erroresValidacion = {};
                error.inner.forEach((e) => {
                    erroresValidacion[e.path] = e.message;
                });
                this.errores = erroresValidacion;
            }
        },


        selectUsuarioFiltro(event) {
            let url = '/cliente/usuario/filtro?filtro=' + event.query;
            axios.get(url).then(response => {
                this.arrayUsuarioFiltro = response.data.usuariodos;
            }).catch(error => {
                console.log(error);
            });
        },
        getDatosUsuarioFiltro(event) {
            if (event.value && event.value.iduse) {
                this.usuariodos_id = event.value.iduse;
                this.usuarioSeleccionadodos = event.value.nombre;
                this.listarPersona(1, this.buscar, this.criterio);
            }
        },
        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            this.listarPersona(page, buscar, criterio);
        },
        registrarPersona(datos) {
            axios.post('/cliente/registrar', datos).then(() => {
                this.cerrarModal();
                this.listarPersona(1, '', 'nombre');
            }).catch(error => {
                console.log(error);
            });
        },
        actualizarPersona(datos) {
            axios.put('/cliente/actualizar', datos).then(() => {
                this.cerrarModal();
                this.listarPersona(1, '', 'nombre');
            }).catch(error => {
                console.log(error);
            });
        },
        cerrarModal() {
            this.modal = false;
            this.tituloModal = '';
            this.errorPersona = 0;
            this.activaredit = false;
            this.idusuario = '';
        },
        abrirModal(modelo, accion, data = []) {
            if (modelo === "persona") {
                this.modal = true;
                if (accion === 'registrar') {
                    this.tituloModal = 'Registrar Cliente';
                    this.tipoAccion = 1;
                    this.datosFormulario = {
                        nombre: '',
                        tipo_documento: '',
                        num_documento: '',
                        complemento: '',
                        direccion: '',
                        telefono: '',
                        email: ''
                    };
                } else if (accion === 'actualizar') {
                    this.tituloModal = 'Actualizar Cliente';
                    this.tipoAccion = 2;
                    this.datosFormulario = { ...data, usuariodos_id: '' };
                    this.activaredit = true;
                    this.verUsuario(data);
                }
            }
        },
        cargarReporteExcel() {
            window.open('/cliente/listarReporteClienteExcel', '_blank');
        },
        getTipoDocumentoText(value) {
            const tipos = {
                '1': 'CI',
                '2': 'CEX',
                '4': 'NIT',
                '3': 'PAS'
            };
            return tipos[value] || '';
        },
        recuperarIdRol() {
            this.rolUsuario = window.userData.rol;
        },

        selectUsuario(search, loading) {
            let me = this;
            loading(true)
            let url = '/cliente/selectUusarioVend?filtro=' + search;
            axios.get(url).then(function (response) {
                //console.log(response.clientes);
                let respuesta = response.data;
                q: search
                me.arrayUsuarioV = respuesta.clientes;
                loading(false)
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        getDatosUsuario(val1) {
            let me = this;
            me.loading = true;

            if (val1 && val1.ID_use) {
                me.idusuario = val1.ID_use;
                me.usuarioSeleccionado = val1.nombre;
            }
        },
        verUsuario(data) {
            this.idusuario = data.usuario;
            let me = this;
            let url = '/cliente/usuario?idusuario=' + this.idusuario;
            axios.get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayDetalleUsuario = respuesta.usuario;
                    me.usuarioSeleccionado = me.arrayDetalleUsuario[0].nombre;

                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        listarDatosuser() {
            axios.get('/user-info')
                .then(response => {
                    const userData = response.data.user;
                    this.usuariodos_id = userData.iduse;
                    this.role_id = userData.idrol;
                    if (this.role_id == 1) {
                        this.usuariodos_id = '';
                        this.listarPersona(1, this.buscar, this.criterio);
                    } else {
                        this.listarPersona(1, this.buscar, this.criterio);
                    }
                })
                .catch(error => {
                    console.error('Error al obtener la información del usuario:', error);
                });
        },

        abrirModalImportar() {
            this.modalImportar = 1;
        },
        cerrarModalImportar() {
            this.modalImportar = 0;
            this.listarPersona(1, '', 'nombre');
        },
    },

    mounted() {
        this.listarDatosuser();
        this.recuperarIdRol();
        this.listarPersona(1, '', 'nombre');
    },
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
