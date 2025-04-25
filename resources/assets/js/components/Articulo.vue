<template>
    <main class="main">
            <!-- Ejemplo de tabla Listado -->
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Artículos armin
                    <button type="button" @click="abrirModal('articulo', 'registrar'); listarPrecio()"
                        class="btn btn-secondary">
                        <i class="icon-plus"></i>&nbsp;Nuevo
                    </button>
                    <button type="button" @click="cargarPdf()" class="btn btn-info">
                        <i class="icon-doc"></i>&nbsp;Reporte
                    </button>

                    <button type="button" @click="abrirModalImportar()" class="btn btn-success">
                        <i class="icon-plus"></i>&nbsp;Importar
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <select class="form-control col-md-3" v-model="criterio">
                                    <option value="nombre">Nombre</option>
                                    <option value="descripcion">Descripción</option>
                                </select>
                                <input type="text" v-model="buscar" @keyup.enter="listarArticulo(1, buscar, criterio)"
                                    class="form-control" placeholder="Texto a buscar">
                                <button type="submit" @click="listarArticulo(1, buscar, criterio)"
                                    class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ACCIONES</th>
                                    <th>CODIGO</th>
                                    <th>NOMBRE COMERCIAL</th>
                                    <th>NOMBRE GENERICO</th>
                                    <th>UNIDADES POR PAQUETE</th>
                                    <th>COSTO UNITARIO</th>
                                    <th>COSTO PAQUETE</th>
                                    <!-- <th>Presio1</th> -->
                                    <th v-for="precio in precios" :key="precio.id">PRECIO {{ precio.nombre_precio }}
                                    </th>

                                    <!-- <th v-if="rolUsuario === 1">Presio venta</th> -->
                                    <!--<th v-if="rolUsuario === 1 && mostrarCostos === 1">PRECIO VENTA</th> -->

                                    <th>LINEA</th>
                                    <th>INDUSTRIA</th>
                                    <th>MARCA</th>

                                    <!-- <th v-if="rolUsuario === 1""v-if="rolusuario !=1"">Stock minimo</th> -->
                                    <th v-if="rolUsuario === 1 && mostrarSaldosStock === 1">STOCK MINIMO</th>
                                    <!-- <th v-if="rolUsuario === 1">Proveedor</th> -->
                                    <th v-if="rolUsuario === 1 && mostrarProveedores === 1">PROVEEDOR</th>
                                    <th>DESCRIPCION</th>
                                    <th>CONTROLADO</th>
                                    <th>GRUPO/FAMILIA</th>
                                    <th>FOTO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="articulo in arrayArticulo" :key="articulo.id">
                                    <td class="sticky-column">
                                        <!-- <button type="button" @click="actualizarAr('articulo', 'actualizar', articulo); calcularPrecio(precio)" -->
                                        <button type="button" @click="abrirModal('articulo', 'actualizar', articulo)"
                                            class="btn btn-warning btn-sm">
                                            <i class="icon-pencil"></i>
                                        </button> &nbsp;
                                        <template v-if="articulo.condicion">
                                            <button type="button" class="btn btn-danger btn-sm"
                                                @click="desactivarArticulo(articulo.id)">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button type="button" class="btn btn-info btn-sm"
                                                @click="activarArticulo(articulo.id)">
                                                <i class="icon-check"></i>
                                            </button>
                                        </template>
                                    </td>
                                    <td v-text="articulo.codigo"></td>
                                    <td v-text="articulo.nombre"></td>
                                    <td v-text="articulo.nombre_generico"></td>
                                    <td v-text="articulo.unidad_envase"></td>
                                    <td>
                                        {{ (articulo.precio_costo_unid * parseFloat(monedaPrincipal[0])).toFixed(2) }}
                                        {{
                        monedaPrincipal[1] }}
                                    </td>
                                    <td>
                                        {{ (articulo.precio_costo_paq * parseFloat(monedaPrincipal[0])).toFixed(2) }} {{
                        monedaPrincipal[1] }}

                                    </td>
                                    <!-- <td v-text="articulo.precio1"></td> -->
                                    <!-- <td>{{ articulo.precio1 ? articulo.precio1 : '0.00' }}</td> -->
                                    <!-- <td v-for="precio in precios" :key="precio.id">{{ precio.porcentage }}</td> -->
                                    <td v-for="(precio, index) in precios" :key="precio.id">
                                        <!-- Mostrar el precio correspondiente según el índice -->
                                        <span v-if="index === 0">

                                            {{ (articulo.precio_uno * parseFloat(monedaPrincipal[0])).toFixed(2) }} {{
                        monedaPrincipal[1] }}

                                        </span>
                                        <span v-if="index === 1">
                                            {{ (articulo.precio_dos * parseFloat(monedaPrincipal[0])).toFixed(2) }} {{
                        monedaPrincipal[1] }}

                                        </span>
                                        <span v-if="index === 2">
                                            {{ (articulo.precio_tres * parseFloat(monedaPrincipal[0])).toFixed(2) }} {{
                        monedaPrincipal[1] }}


                                        </span>
                                        <span v-if="index === 3">
                                            {{ (articulo.precio_cuatro * parseFloat(monedaPrincipal[0])).toFixed(2) }}
                                            {{
                        monedaPrincipal[1] }}

                                        </span>
                                    </td>

                                    <!-- <td v-if="rolUsuario === 1" v-text="articulo.precio_venta"></td> -->
                                    <!--<td v-if="rolUsuario === 1 && mostrarCostos === 1">
                                        {{ (articulo.precio_venta * parseFloat(monedaPrincipal[0])).toFixed(2) }} {{
                        monedaPrincipal[1] }}

                                    </td>-->

                                    <td v-text="articulo.nombre_categoria"></td>
                                    <td v-text="articulo.nombre_industria"></td>
                                    <td v-text="articulo.nombre_marca"></td>

                                    <!-- <td v-if="rolUsuario === 1" v-text="articulo.stock"></td> -->
                                    <td v-if="rolUsuario === 1 && mostrarSaldosStock === 1" v-text="articulo.stock">
                                    </td>

                                    <!-- <td v-if="rolUsuario === 1" v-text="articulo.nombre_proveedor"></td> -->
                                    <td v-if="rolUsuario === 1 && mostrarProveedores === 1"
                                        v-text="articulo.nombre_proveedor"></td>

                                    <td v-text="articulo.descripcion"></td>
                                    <td style="text-align: center;">
                                        <div v-if="articulo.condicion">

                                            <span style="center" class="badge badge-success">Si</span>
                                        </div>
                                        <div v-else>
                                            <span class="badge badge-danger">No</span>

                                        </div>

                                    </td>
                                    <td v-text="articulo.nombre_grupo"></td>
                                    <td class="text-center">
                                        <img :src="'img/articulo/' + articulo.fotografia + '?t=' + new Date().getTime()"
                                            width="50" height="50" v-if="articulo.fotografia" ref="imagen">
                                        <img :src="'img/articulo/' + 'defecto.jpg'" width="50" height="50" v-else
                                            ref="imagen">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#"
                                    @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)">Ant</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page"
                                :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)"
                                    v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#"
                                    @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)">Sig</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <!-- Fin ejemplo de tabla Listado -->
       
        <!-- MODAL LISTADO DE MARCAS -->

        <!-- contenido del modal -->

        <!--Inicio del modal agregar/actualizar-->
        <div class="modal" tabindex="-1" :class="{ 'mostrar': modal }" role="dialog" aria-labelledby="myModalLabel"
            style="display: ;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal"></h4>
                        <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form @submit.prevent="enviarFormulario">

                        <div class="modal-body">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div>
                                        <label for="" class="font-weight-bold">Nombre del Productos <span
                                                class="text-danger">*</span></label>
                                        <input type="text" v-model="datosFormulario.nombre" class="form-control"
                                            placeholder="Ej. Ibuprofeno 400 mg (20 comprimidos)"
                                            :class="{ 'is-invalid': errores.nombre }" @input="validarCampo('nombre')" />
                                        <p class="text-danger" v-if="errores.nombre">{{ errores.nombre }}</p>
                                    </div>
                                    <div>

                                        <label for="" class="font-weight-bold">Nombre Genérico
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" v-model="datosFormulario.nombre_generico"
                                            class="form-control" placeholder="Ej. Ibuprofeno"
                                            :class="{ 'is-invalid': errores.nombre_generico }"
                                            @input="validarCampo('nombre_generico')" />
                                        <p class="text-danger" v-if="errores.nombre_generico">{{ errores.nombre_generico
                                            }}
                                        </p>



                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="container ">
                                        <div class="row">
                                            <div class="d-flex justify-content-center">
                                                <div v-if="!imagen" class="bg-light p-5 rounded">
                                                    <i class="fa fa-camera fa-2x" style="color:#6e6e6e"
                                                        aria-hidden="true"></i>
                                                </div>
                                                <figure v-else>
                                                    <img :src="imagen" width="140" height="140" alt="Foto articulo">
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-group mt-2">
                                        <input type="file" @change="obtenerFotografia" class="form-control"
                                            placeholder="fotografia" ref="fotografiaInput">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">

                                    <label for="" class="font-weight-bold">Descripción del Producto <span
                                            class="text-danger">*</span></label>
                                    <textarea rows="3" type="text" v-model="datosFormulario.descripcion"
                                        class="form-control"
                                        placeholder="Ej. Alivio rápido para el dolor de cabeza y fiebre"
                                        :class="{ 'is-invalid': errores.descripcion }"
                                        @input="validarCampo('descripcion')" />
                                    <p class="text-danger" v-if="errores.descripcion">{{ errores.descripcion }}</p>
                                    <label for="" class="font-weight-bold">Código Alfanumérico
                                        <span class="font-weight-normal text-sm text-secondary">(Opcional)</span>

                                    </label>
                                    <input type="text" v-model="datosFormulario.codigo_alfanumerico"
                                        class="form-control" placeholder="Ej. ABC123"
                                        :class="{ 'is-invalid': errores.codigo_alfanumerico }"
                                        @input="validarCampo('codigo_alfanumerico')" />
                                    <p class="text-danger" v-if="errores.codigo_alfanumerico">{{
                        errores.codigo_alfanumerico
                    }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Código del Producto <span
                                            class="text-danger">*</span></label>
                                    <input type="text" v-model="datosFormulario.codigo" class="form-control"
                                        placeholder="Ej. SKU123" :class="{ 'is-invalid': errores.codigo }"
                                        @input="validarCampo('codigo')" />
                                    <p class="text-danger" v-if="errores.codigo">{{ errores.codigo }}</p>

                                    <div class="d-flex mt-4 justify-content-center"
                                        style="width:250px;overflow-x: auto;">
                                        <barcode :value="datosFormulario.codigo" :options="{ format: 'EAN-13' }">
                                        </barcode>
                                    </div>

                                </div>

                            </div>
                            <div class="form-group row">

                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Descripción de Fabricación
                                        <span class="font-weight-normal text-sm text-secondary">(Opcional)</span>

                                    </label>
                                    <textarea rows="3" type="text" v-model="datosFormulario.descripcion_fabrica"
                                        class="form-control" placeholder="Ej. Producto fabricado por Laboratorios XYZ"
                                        :class="{ 'is-invalid': errores.descripcion_fabrica }"
                                        @input="validarCampo('descripcion_fabrica')" />
                                    <p class="text-danger" v-if="errores.descripcion_fabrica">{{
                        errores.descripcion_fabrica
                    }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Proveedor <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione un proveedor"
                                            disabled v-model="proveedorseleccionada.nombre"
                                            :class="{ 'is-invalid': errores.idproveedor }"
                                            @input="validarCampo('codigo')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal2('Proveedors')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idproveedor">{{ errores.idproveedor }}</p>
                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Linea <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione una linea"
                                            disabled v-model="lineaseleccionada.nombre"
                                            :class="{ 'is-invalid': errores.idcategoria }"
                                            @input="validarCampo('codigo')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal2('Lineas')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idcategoria">{{ errores.idcategoria }}</p>
                                </div>

                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Marca <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione una marca"
                                            disabled v-model="marcaseleccionada.nombre"
                                            :class="{ 'is-invalid': errores.idmarca }" @input="validarCampo('idmarca')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal2('Marcas')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idmarca">{{ errores.idmarca }}</p>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Industria <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione una industria"
                                            disabled v-model="industriaseleccionada.nombre"
                                            :class="{ 'is-invalid': errores.idindustria }"
                                            @input="validarCampo('codigo')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal2('Industrias')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idindustria">{{ errores.idindustria }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Grupo <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione un grupo"
                                            disabled v-model="gruposeleccionada.nombre_grupo"
                                            :class="{ 'is-invalid': errores.idgrupo }" @input="validarCampo('codigo')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal2('Grupos')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idgrupo">{{ errores.idgrupo }}</p>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label for="" class="font-weight-bold">Stock minimo <span
                                            class="text-danger">*</span></label>

                                    <div class="input-group">
                                        <input type="number" v-model="datosFormulario.stock" class="form-control"
                                            placeholder="Ej. 123456789" :class="{ 'is-invalid': errores.stock }"
                                            @input="validarCampo('stock')">
                                        <select class="custom-select" v-model="tipo_stock">
                                            <option value="unidades">Unidades</option>
                                            <option value="paquetes">Paquetes</option>
                                        </select>

                                    </div>

                                    <p class="text-danger" v-if="errores.stock">{{ errores.stock }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label for="" class="font-weight-bold">Unidades por paquete <span
                                            class="text-danger">*</span></label>
                                    <input type="number" v-model="datosFormulario.unidad_envase" class="form-control"
                                        placeholder="unidades por paquete"
                                        :class="{ 'is-invalid': errores.unidad_envase }"
                                        @input="validarCampo('unidad_envase')" />
                                    <p class="text-danger" v-if="errores.unidad_envase">{{ errores.unidad_envase }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label for="" class="font-weight-bold">Medida <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" placeholder="Seleccione un medida"
                                            disabled v-model="medidaseleccionada.descripcion_medida"
                                            :class="{ 'is-invalid': errores.idmedida }" @input="validarCampo('codigo')">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button"
                                                @click="abrirModal6('Medidas')">...</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.idmedida">{{ errores.idmedida }}</p>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Precio unitario <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group ">
                                        <input type="number" v-model="datosFormulario.precio_costo_unid"
                                            class="form-control" placeholder="Ej. 123456789"
                                            :class="{ 'is-invalid': errores.precio_costo_unid }"
                                            @input="validarCampo('precio_costo_unid')" />
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" @click="calcularPrecioCostoUnid"
                                                type="button">Calcular</button>
                                        </div>
                                    </div>
                                    <p class="text-danger" v-if="errores.precio_costo_unid">{{ errores.precio_costo_unid
                                        }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label for="" class="font-weight-bold">Precio paquete <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group ">
                                        <input type="number" v-model="datosFormulario.precio_costo_paq"
                                            class="form-control" placeholder="Ej. precio_costo_paq@dominio.com"
                                            :class="{ 'is-invalid': errores.precio_costo_paq }"
                                            @input="validarCampo('precio_costo_paq')" />
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" @click="calcularPrecioCostoPaq"
                                                type="button">Calcular</button>
                                        </div>
                                    </div>



                                    <p class="text-danger" v-if="errores.precio_costo_paq">{{ errores.precio_costo_paq
                                        }}
                                    </p>
                                </div>

                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class=" d-flex align-items-center">
                                        <label for="" class="font-weight-bold mb-0">
                                            Fecha de vencimiento 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="custom-select ml-2" v-model="fechaVencimientoSeleccion">
                                            <option value="1">Si</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" v-if="tipoAccion == 1">
                                    <div class=" d-flex align-items-center">
                                        <label for="" class="font-weight-bold mb-0">
                                            Agregar a stock 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="custom-select ml-2" v-model="agregarStock">
                                            <option value="si">Si</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div v-if="agregarStock ==='si' && tipoAccion==1" class="form-group row">
                                    <div class="col-md-4">
                                        <label for="" class="font-weight-bold">Unidad Stock <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" v-model="unidadStock" class="form-control"
                                            :class="{ 'is-invalid': erroresinventario.unidadStock }"
                                            @input="validarCampoInventario('unidadStock')" min="0" />
                                            <p class="text-danger" v-if="erroresinventario.unidadStock">{{ erroresinventario.unidadStock }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="" class="font-weight-bold">Fecha de Vencimiento <span
                                                class="text-danger">*</span></label>
                                        <input v-if="fechaVencimientoSeleccion == '0'" type="date" v-model="fechaPorDefecto" class="form-control" readonly/>
                                        <input v-else type="date" v-model="fechaPorDefecto" class="form-control"
                                            :class="{ 'is-invalid': erroresinventario.fechaVencimientoAlmacen }"
                                            @input="validarCampoInventario('fechaVencimientoAlmacen')" />
                                        <p class="text-danger" v-if="erroresinventario.fechaVencimientoAlmacen">{{ erroresinventario.fechaVencimientoAlmacen }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="" class="font-weight-bold">Almacen <span class="text-danger">*</span></label>

                                        <select class="form-control" v-model="AlmacenSeleccionado"
                                                :class="{ 'is-invalid': errores.AlmacenSeleccionado }"
                                                @change="validarCampoInventario('AlmacenSeleccionado')">
                                            <option value="0" disabled selected>Seleccione</option>
                                            <option v-for="opcion in arrayAlmacenes" :key="opcion.id" :value="opcion.id">{{ opcion.nombre_almacen }}</option>
                                        </select>
                                        <p class="text-danger" v-if="erroresinventario.AlmacenSeleccionado">{{ erroresinventario.AlmacenSeleccionado }}</p>
                                    </div>

                            </div>


                            <div v-for="(precio, index) in precios" :key="precio.id" class="d-flex form-group row">

                                <label for="" class="font-weight-bold col-md-3 form-control-label">{{
                        precio.nombre_precio
                    }} :
                                </label>
                                <div class="col-md-4">
                                    <div v-if="index === 0" class=" input-group" style="width: 150px">

                                        <input type="text" class="form-control" placeholder="Precio"
                                            v-model="precio_uno">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ monedaPrincipal[1] }}</span>
                                        </div>
                                    </div>
                                    <div v-if="index === 1" class=" input-group" style="width: 150px">

                                        <input type="text" class="form-control" placeholder="Precio"
                                            v-model="precio_dos">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ monedaPrincipal[1] }}</span>
                                        </div>
                                    </div>
                                    <div v-if="index === 2" class=" input-group" style="width: 150px">

                                        <input type="text" class="form-control" placeholder="Precio"
                                            v-model="precio_tres">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ monedaPrincipal[1] }}</span>
                                        </div>
                                    </div>
                                    <div v-if="index === 3" class=" input-group" style="width: 150px">

                                        <input type="text" class="form-control" placeholder="Precio"
                                            v-model="precio_cuatro">
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ monedaPrincipal[1] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="cold-md-3 input-group" style="width: 150px">
                                    <input type="text" class="form-control" placeholder="Porcentaje"
                                        :value="precio.porcentage">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary"
                                        @click="calcularPrecio(precio, index)">Calcular</button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" @click="cerrarModal()">Cerrar</button>
                            <button type="submit" v-if="tipoAccion == 1" class="btn btn-success">Guardar</button>
                            <button type="submit" v-if="tipoAccion == 2" class="btn btn-success">Actualizar</button>
                        </div>
                    </form>

                </div>
                <!-- /.modal-content -->
            </div>


            <!-- /.modal-dialog -->
        </div>

        <!-- MODAL PARA LA LISTA DE MEDIDA -->
        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal6 }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal6"></h4>
                        <button type="button" class="close" @click="modal6 = false" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <button v-show="tituloModal6 == 'Medidas'" type="button"
                                        @click="abrirModal7('medida', 'registrarMed')" class="btn btn-secondary">
                                        <i class="icon-plus"></i>&nbsp;Nuevo
                                    </button>
                                    <!--button type="submit" @click="listarArticulo(buscarA, criterioA)"
                                class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button-->
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th style="display: none;">ID</th>
                                        <th>Opciones</th>
                                        <th>Medida</th>
                                        <th>Descripción Corta</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="arrayelemento in arrayBuscador" :key="arrayelemento.id">
                                        <td>
                                            <button type="button" @click="seleccionar2(arrayelemento)"
                                                class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                            <button v-if="tituloModal6 == 'Medidas'" type="button"
                                                @click="abrirModal7('medida', 'actualizarMed', arrayelemento)"
                                                class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                            </button> &nbsp;
                                        </td>
                                        <td v-if="mostrarElemento" v-text="arrayelemento.id"></td>
                                        <td v-text="arrayelemento.descripcion_medida"></td>
                                        <td v-text="arrayelemento.descripcion_corta"></td>
                                        <td v-if="tituloModal6 == 'Medidas'">
                                            <div v-if="arrayelemento.estado">
                                                <span class="badge badge-success">Activo</span>
                                            </div>
                                            <div v-else>
                                                <span class="badge badge-danger">Desactivado</span>
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination">
                                <li class="page-item" v-if="paginationMedida.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMedida(paginationMedida.current_page - 2, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumberMedida" :key="page"
                                    :class="[page == isActivedM ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMedida(page, buscar, criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="paginationMedida.current_page < paginationMedida.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMedida(paginationMedida.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modal6 = false">Cerrar</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- HASTA AQUI EL MODAL DE LISTA MEDIDA -->

        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal2 }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal2"></h4>
                        <button type="button" class="close" @click="modal2 = false" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3">
                                        <option v-if="tituloModal2 !== 'Grupos'" value="nombre">Nombre</option>
                                        <option v-else-if="tituloModal2 == 'Grupos'" value="nombre_grupo">Grupo</option>
                                        <!-- <option v-if="tituloModal2=='Grupos'" value="nombre_grupo">Nombre_grupo</option> -->
                                    </select>
                                    <input v-if="tituloModal2 == 'Marcas'" type="text" v-model="buscarA"
                                        @keyup="listarMarca(1, buscarA, criterioA)" class="form-control"
                                        placeholder="Texto a buscar">
                                    <input v-if="tituloModal2 == 'Industrias'" type="text" v-model="buscarA"
                                        @keyup="listarIndustria(1, buscarA, criterioA)" class="form-control"
                                        placeholder="Texto a buscar">
                                    <input v-if="tituloModal2 == 'Lineas'" type="text" v-model="buscarA"
                                        @keyup="listarLinea(1, buscarA, criterioA)" class="form-control"
                                        placeholder="Texto a buscar">
                                    <input v-if="tituloModal2 == 'Proveedors'" type="text" v-model="buscarA"
                                        @keyup="listarproveedor(1, buscarA, criterioA)" class="form-control"
                                        placeholder="Texto a buscar">
                                    <input v-if="tituloModal2 == 'Grupos'" type="text" v-model="buscarA"
                                        @keyup="listargrupo(1, buscarA, criterioA)" class="form-control"
                                        placeholder="Texto a buscar">

                                    <button v-show="tituloModal2 == 'Industrias'" type="button"
                                        @click="abrirModal3('industria', 'registrarInd')" class="btn btn-secondary">
                                        <i class="icon-plus"></i>&nbsp;Nuevo
                                    </button>
                                    <button v-show="tituloModal2 == 'Marcas'" type="button"
                                        @click="abrirModal3('Marca', 'registrarMar')" class="btn btn-secondary">
                                        <i class="icon-plus"></i>&nbsp;Nuevo
                                    </button>
                                    <button v-show="tituloModal2 == 'Lineas'" type="button"
                                        @click="abrirModal3('Linea', 'registrarLin')" class="btn btn-secondary">
                                        <i class="icon-plus"></i>&nbsp;Nuevo
                                    </button>
                                    <button v-show="tituloModal2 == 'Proveedors'" type="button"
                                        @click="abrirModal3('Proveedor', 'registrarProv')" class="btn btn-secondary">
                                        <i class="icon-plus"></i>&nbsp;Nuevo
                                    </button>
                                    <button v-show="tituloModal2 == 'Grupos'" type="button"
                                        @click="abrirModal3('Grupo', 'registrarGrupo')" class="btn btn-secondary">
                                        <i class="icon-plus"></i>&nbsp;Nuevo
                                    </button>
                                    <!--button type="submit" @click="listarArticulo(buscarA, criterioA)"
                                        class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button-->
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" v-if="tituloModal2 !== 'Grupos'">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Id</th>

                                        <th>Nombre</th>
                                        <!-- <th>Estado</th> -->
                                        <th>
                                            <div v-if="tituloModal2 == 'Proveedors'">
                                                Nit
                                            </div>
                                            <div v-else>
                                                Estado
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="arrayelemento in arrayBuscador" :key="arrayelemento.id">
                                        <td>
                                            <button type="button" @click="seleccionar(arrayelemento)"
                                                class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                            <button v-if="tituloModal2 == 'Industrias'" type="button"
                                                @click="abrirModal3('industria', 'actualizarInd', arrayelemento)"
                                                class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                            </button> &nbsp;
                                            <button v-if="tituloModal2 == 'Marcas'" type="button"
                                                @click="abrirModal3('Marca', 'actualizar', arrayelemento)"
                                                class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                            </button> &nbsp;
                                            <button v-if="tituloModal2 == 'Lineas'" type="button"
                                                @click="abrirModal3('Linea', 'actualizarLin', arrayelemento)"
                                                class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                            </button> &nbsp;
                                            <button v-if="tituloModal2 == 'Proveedors'" type="button"
                                                @click="abrirModal3('Proveedor', 'actualizarProv', arrayelemento)"
                                                class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                            </button> &nbsp;

                                        </td>
                                        <td v-text="arrayelemento.id"></td>
                                        <!-- <div v-if="tituloModal2=='Grupos'">
                                            <td  v-text="arrayelemento.nombre_grupo"></td>
                                        </div> -->
                                        <td v-text="arrayelemento.nombre"></td>
                                        <td v-if="tituloModal2 == 'Industrias'">
                                            <div v-if="arrayelemento.estado">
                                                <span class="badge badge-success">Activo</span>
                                            </div>
                                            <div v-else>
                                                <span class="badge badge-danger">Desactivado</span>
                                            </div>
                                        </td>
                                        <td v-if="tituloModal2 == 'Marcas' || tituloModal2 == 'Lineas'">
                                            <div v-if="arrayelemento.condicion">
                                                <span class="badge badge-success">Activo</span>
                                            </div>
                                            <div v-else>
                                                <span class="badge badge-danger">Desactivado</span>
                                            </div>
                                        </td>
                                        <div v-if="tituloModal2 == 'Proveedors'">
                                            <td v-text="arrayelemento.num_documento"></td>
                                        </div>
                                    </tr>
                                </tbody>
                            </table>
                            <!--###########################################################-->
                            <table class="table table-bordered table-striped table-sm"
                                v-else-if="tituloModal2 == 'Grupos'">
                                <thead>
                                    <tr>
                                        <th>Opciones</th>
                                        <th>Id</th>
                                        <th>Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="arrayelemento in arrayBuscador" :key="arrayelemento.id">
                                        <td>
                                            <button type="button" @click="seleccionar(arrayelemento)"
                                                class="btn btn-success btn-sm">
                                                <i class="icon-check"></i>
                                            </button>
                                            <button v-if="tituloModal2 == 'Grupos'" type="button"
                                                @click="abrirModal3('Grupo', 'actualizarGrupo', arrayelemento)"
                                                class="btn btn-warning btn-sm">
                                                <i class="icon-pencil"></i>
                                            </button> &nbsp;
                                        </td>
                                        <td v-text="arrayelemento.id"></td>
                                        <td v-text="arrayelemento.nombre_grupo"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <!--##################################################################3-->
                        </div>
                        <nav v-if="tituloModal2 == 'Marcas'">
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMarca(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMarca(page, buscar, criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaMarca(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                        <nav v-else-if="tituloModal2 == 'Lineas'">
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaLinea(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaLinea(page, buscar, criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaLinea(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                        <nav v-else-if="tituloModal2 == 'Industrias'">
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaIndustria(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActivedMar ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaIndustria(page, buscar, criterio)"
                                        v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaIndustria(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                        <nav v-else-if="tituloModal2 == 'Proveedors'">
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaProveedor(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActivedMar ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaProveedor(page, buscar, criterio)"
                                        v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaProveedor(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                        <nav v-else-if="tituloModal2 == 'Grupos'">
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaGrupo(pagination.current_page - 1, buscar, criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page"
                                    :class="[page == isActivedMar ? 'active' : '']">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaGrupo(page, buscar, criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#"
                                        @click.prevent="cambiarPaginaGrupo(pagination.current_page + 1, buscar, criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modal2 = false">Cerrar</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!--Fin del modal-->
        <!--######################################-aqui registro de industria,Marca,Linea#####################-->
        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal3 }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal3"></h4>
                        <button type="button" class="close" @click="cerrarModal3()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div v-if="tituloModal2 !== 'Proveedors'" class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div v-if="tituloModal2 !== 'Grupos' && tituloModal2 !== 'Lineas'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="nombre" class="form-control1"
                                        :placeholder="placeholderInput()">
                                </div>
                            </div>
                            <div v-else-if="tituloModal2 == 'Grupos'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre Grupo</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="nombre_grupo" class="form-control"
                                        :placeholder="placeholderInput()">
                                </div>
                            </div>
                            <div v-if="tituloModal2 == 'Lineas'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre Linea</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="nombreLinea" class="form-control1"
                                        :placeholder="placeholderInput('nombre')">
                                </div>
                                <label class="col-md-3 form-control-label" for="text-input">Descripcion</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="descripcion" class="form-control1"
                                        :placeholder="placeholderInput('descripcion')">
                                </div>
                                <label class="col-md-3 form-control-label" for="text-input">Codigo</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="codigoProductoSin" class="form-control1"
                                        :placeholder="placeholderInput('codigoProductoSin')">
                                </div>
                            </div>
                            <!-- prueba de habilitar  -->
                            <div v-if="tituloModal2 == 'Industrias'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Estado</label>
                                <div class="col-md-9">
                                    <input type="checkbox" v-model="estado" v-bind:value="1" />
                                    <span>{{ estado ? 'Habilitar' : 'Inhabilitado' }}</span>
                                </div>
                            </div>
                            <div v-if="tituloModal2 == 'Marcas'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Estado</label>
                                <div class="col-md-9">
                                    <input type="checkbox" v-model="condicion" v-bind:value="1" />
                                    <span>{{ condicion ? 'Habilitar' : 'Inhabilitado' }}</span>
                                </div>
                            </div>
                            <div v-if="tituloModal2 == 'Lineas'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Estado</label>
                                <div class="col-md-9">
                                    <input type="checkbox" v-model="condicion" v-bind:value="1" />
                                    <span>{{ condicion ? 'Habilitar' : 'Inhabilitado' }}</span>
                                </div>
                            </div>

                            <div v-show="errorIndustria" class="form-group row div-error">
                                <div class="text-center text-error">
                                    <div v-for="error in errorMostrarMsjIndustria" :key="error" v-text="error"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--############################## registro de proveedor####################################3---->
                    <div v-else-if="tituloModal2 == 'Proveedors'" class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Nombre (*)</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="nombre" class="form-control"
                                        placeholder="Nombre de la persona">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Tipo Documento</label>
                                <div class="col-md-9">
                                    <select v-model="tipo_documento" class="form-control">
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                        <option value="PASS">PASS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Número</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="num_documento" class="form-control"
                                        placeholder="Número de documento">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Dirección</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="direccion" class="form-control" placeholder="Dirección">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Teléfono</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="telefono" class="form-control" placeholder="Teléfono">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Email</label>
                                <div class="col-md-9">
                                    <input type="email" v-model="email" class="form-control" placeholder="Email">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Contacto</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="contacto" class="form-control"
                                        placeholder="Nombre del contacto">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="email-input">Teléfono de
                                    contacto</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="telefono_contacto" class="form-control"
                                        placeholder="Teléfono del contacto">
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--######################################-hasta aqui###############################-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal3()">Cerrar</button>
                        <!-- <button type="button" class="btn btn-secondary" @click="modal3=0">Cerrar</button> -->
                        <button type="button" v-if="tipoAccion2 == 3" class="btn btn-primary"
                            @click="registrarIndustria()">Guardar</button>
                        <button type="button" v-if="tipoAccion2 == 4" class="btn btn-primary"
                            @click="actualizarIndustria()">Actualizar</button>
                        <button type="button" v-if="tipoAccion2 == 5" class="btn btn-primary"
                            @click="registrarMarca()">Guardar</button>
                        <button type="button" v-if="tipoAccion2 == 6" class="btn btn-primary"
                            @click="actualizarMarca()">Actualizar</button>
                        <button type="button" v-if="tipoAccion2 == 7" class="btn btn-primary"
                            @click="registrarLinea()">Guardar</button>
                        <button type="button" v-if="tipoAccion2 == 8" class="btn btn-primary"
                            @click="actualizarLinea()">Actializar</button>
                        <button type="button" v-if="tipoAccion2 == 9" class="btn btn-primary"
                            @click="registrarProveedor()">Guardar</button>
                        <button type="button" v-if="tipoAccion2 == 10" class="btn btn-primary"
                            @click="actualizarProveedor()">Actializar</button>
                        <button type="button" v-if="tipoAccion2 == 11" class="btn btn-primary"
                            @click="registrarGrupo()">Guardar</button>
                        <button type="button" v-if="tipoAccion2 == 12" class="btn btn-primary"
                            @click="actualizarGrupo()">Actializar</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!--Fin del modal-->
        <!--######################################hasta aqui registro de industria#####################-->

        <!--######################################-aqui registro de medida#####################-->
        <div class="modal " tabindex="-1" :class="{ 'mostrar': modal7 }" role="dialog" aria-labelledby="myModalLabel"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="tituloModal7"></h4>
                        <button type="button" class="close" @click="cerrarModal6()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Medida</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="descripcion_medida" class="form-control"
                                        :placeholder="placeholderInput()">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Descripción Corta</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="descripcion_corta" class="form-control"
                                        :placeholder="placeholderInput()">
                                </div>
                            </div>
                            <!-- prueba de habilitar  -->
                            <div v-if="tituloModal2 == 'Medidas'" class="form-group row">
                                <label class="col-md-3 form-control-label" for="text-input">Estado</label>
                                <div class="col-md-9">
                                    <input type="checkbox" v-model="estado" v-bind:value="1" />
                                    <span>{{ estado ? 'Habilitar' : 'Inhabilitado' }}</span>
                                </div>
                            </div>

                            <div v-show="errorMedida" class="form-group row div-error">
                                <div class="text-center text-error">
                                    <div v-for="error in errorMostrarMsjMedida" :key="error" v-text="error"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModal7()">Cerrar</button>
                        <!-- <button type="button" class="btn btn-secondary" @click="modal3=0">Cerrar</button> -->
                        <button type="button" v-if="tipoAccion6 == 6" class="btn btn-primary"
                            @click="registrarMedida()">Guardar</button>
                        <button type="button" v-if="tipoAccion6 == 7" class="btn btn-primary"
                            @click="actualizarMedida()">Actualizar</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <div v-if="modalImportar">
            <ImportarExcel @cerrar="cerrarModalImportar"></ImportarExcel>
        </div>

    </main>
</template>

<script>

import { esquemaArticulos, esquemaInventario } from '../constants/validations';

import VueBarcode from 'vue-barcode';
export default {
    data() {
        return {
            tipo_stock: "paquetes",
            agregarStock: 'no',
            unidadStock: null,
            idarticulo : 0,
            fechaVencimientoAlmacen: null,
            fechaVencimientoSeleccion: '0',
            arrayAlmacenes: [],
            AlmacenSeleccionado: null,
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
                fechaVencimientoSeleccion:'0',
                

            },
            datosFormularioInventario : {
                AlmacenSeleccionado : null ,
                fechaVencimientoAlmacen : null,
                unidadStock : null
            },
            errores: {},
            erroresinventario :{},
            monedaPrincipal: [],

            modalImportar: 0,

            criterioA: 'nombre',
            buscarA: '',
            tituloModal2: '',
            marcaseleccionada: [],
            industriaseleccionada: [],
            lineaseleccionada: [],
            proveedorseleccionada: [],
            gruposeleccionada: [],
            nombre_grupo: '',

            modal2: false,
            modal6: false,
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
            //validaion para inputs
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
            //aumente esto
            unidad_envase: 0,
            precio_costo_unid: 0,
            precio_costo_paq: 0,
            //hasta aqui
            precios: [],
            //precioss: [],
            precio: '',//aumente 5julio
            //mostrarPrecios: false,
            //precioCount: 0,
            //aumento 13_junio
            precio_uno: 0,
            precio_dos: 0,
            precio_tres: 0,
            precio_cuatro: 0,
            //hasta aqui
            //--------proveedor para almacer el registrado y editar------
            proveedor_id: 0,
            tipo_documento: 'DNI',
            num_documento: '',
            direccion: '',
            telefono: '',
            email: '',
            contacto: '',
            telefono_contacto: '',
            //--------hasta aqui-----------------
            //--grupo--
            nombre_grupo: '',
            grupo_id: 0,
            //---hasta aqui
            precio_venta: 0,
            costo_compra: 0,

            stock: 5,
            nombre_persona: '',
            descripcion: '',
            fotografia: '',
            fotoMuestra: null,
            arrayArticulo: [],
            arrayBuscador: [],
            modal: 0,

            tituloModal: '',
            tipoAccion: 0,
            tipoAccion2: 0,
            tipoAccion6: 0,
            errorArticulo: 0,
            errorMostrarMsjArticulo: [],
            //------registro industia, marcas--
            modal3: 0,
            tituloModal3: '',
            industria_id: 0,
            marca_id: 0,
            linea_id: 0,
            estado: 1,
            condicion: 1,
            nombre_industria: '',
            mostrarElemento: '',
            errorIndustria: 0,
            errorMostrarMsjIndustria: [],
            //--------hasta aqui---
            pagination: this.createPaginationObject(),
            paginationMedida: this.createPaginationObject(),
            offset: {
                pagination: 3,
                paginationMedida: 3,
            },
            criterio: 'nombre',
            buscar: '',
            arrayCategoria: [],

            //CONFIGURACIONES
            mostrarSaldosStock: '',
            mostrarProveedores: '',
            mostrarCostos: '',
            rolUsuario: '',

            //medida
            arrayMedida: [],
            errorMedida: 0,
            errorMostrarMsjMedida: [],
            modal7: 0,
            tituloModal6: '',
            tituloModal7: '',
            medida_id: 0,
            descripcion_medida: '',
            descripcion_corta: '',
            medidaseleccionada: [],
        }
    },
    components: {
        'barcode': VueBarcode
    },
    computed: {
        isActived: function () {
            return this.pagination.current_page;
        },
        isActivedM: function () {
            return this.pagination.current_page;
        },
        isActivedMar: function () {
            return this.pagination.current_page;
        },

        pagesNumber: function () {
            return this.calculatePages(this.pagination, this.offset.pagination);
        },
        pagesNumberMedida: function () {
            return this.calculatePages(this.paginationMedida, this.offset.paginationMedida);
        },
        imagen() {
            return this.fotoMuestra;
        },
        fechaPorDefecto() {
            if (this.fechaVencimientoSeleccion == '0' && !this.fechaVencimientoAlmacen) {
                this.fechaVencimientoAlmacen = '2099-12-31'
                return this.fechaVencimientoAlmacen;
            }else {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0'); // Los meses son indexados desde 0
                const day = String(today.getDate()).padStart(2, '0');
                this.fechaVencimientoAlmacen = `${year}-${month}-${day}`;
            }
            return this.fechaVencimientoAlmacen;
        }
    },
    watch: {
        previewCsv: 'parseCsv', // Llama a parseCsv cuando previewCsv cambie
    },
    methods: {
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
        asignarCampos() {
            this.datosFormulario.idcategoria = this.lineaseleccionada.id
            this.datosFormulario.idmarca = this.marcaseleccionada.id
            this.datosFormulario.idproveedor = this.proveedorseleccionada.id
            this.datosFormulario.idindustria = this.industriaseleccionada.id
            this.datosFormulario.idmedida = this.medidaseleccionada.id
            this.datosFormulario.idgrupo = this.gruposeleccionada.id
            this.datosFormulario.precio_costo_unid = this.convertDolar(this.datosFormulario.precio_costo_unid);
            this.datosFormulario.precio_costo_paq = this.convertDolar(this.datosFormulario.precio_costo_paq);
            this.datosFormulario.precio_venta = this.convertDolar(this.datosFormulario.precio_venta);

            this.datosFormulario.precio_uno = this.convertDolar(this.precio_uno);
            this.datosFormulario.precio_dos = this.convertDolar(this.precio_dos);
            this.datosFormulario.precio_tres = this.convertDolar(this.precio_tres);
            this.datosFormulario.precio_cuatro = this.convertDolar(this.precio_cuatro);
            this.datosFormulario.costo_compra = this.convertDolar(this.datosFormulario.costo_compra);
            this.datosFormulario.fechaVencimientoSeleccion = this.fechaVencimientoSeleccion;
        },
        async validarCampo(campo) {
            this.asignarCampos();
            try {
                await esquemaArticulos.validateAt(campo, this.datosFormulario);
                this.errores[campo] = null;
            } catch (error) {
                this.errores[campo] = error.message;
            }
            /*if (this.agregarStock === 'si') {
                this.datosFormularioInventario.AlmacenSeleccionado = this.AlmacenSeleccionado;
                this.datosFormularioInventario.unidadStock = this.unidadStock;
                this.datosFormularioInventario.fechaVencimientoAlmacen = this.fechaVencimientoAlmacen;
                try {
                    await esquemaInventario.validateAt(campo, this.datosFormularioInventario);
                    this.errores[campo] = null;
                } catch (error) {
                    this.errores[campo] = error.message;
                }
            }*/

        },
        async validarCampoInventario(campo){
            this.asignarCampos();
            try {
                await esquemaInventario.validateAt(campo, this.datosFormularioInventario);
                this.erroresinventario[campo] = null;
            } catch (error) {
                this.erroresinventario[campo] = error.message;
            }
        },
        async enviarFormulario() {
            this.asignarCampos();

            console.log("UNIDAD STOCK ", this.unidadStock);
            console.log("ALMACEN ", this.AlmacenSeleccionado);
            console.log("agregar ", this.agregarStock);

            if (this.agregarStock === 'si') {
                console.log("Asignando valores adicionales al formulario");
                this.datosFormularioInventario.AlmacenSeleccionado = this.AlmacenSeleccionado;
                this.datosFormularioInventario.unidadStock = this.unidadStock;
                this.datosFormularioInventario.fechaVencimientoAlmacen = this.fechaVencimientoAlmacen;
            }

            console.log("DATOS FORMULARIO ANTES DE VALIDAR: ", this.datosFormulario);
            console.log("DATOS FORMULARIO ANTES DE VALIDAR: ", this.datosFormularioInventario);

            let validacionExitosa = true;
            let validacionInventarioExitosa = true;

            try {
                await esquemaArticulos.validate(this.datosFormulario, { abortEarly: false });
                console.log("Validación de esquemaArticulos exitosa");
            } catch (error) {
                validacionExitosa = false;
                const erroresValidacion = {};
                error.inner.forEach((e) => {
                    erroresValidacion[e.path] = e.message;
                });
                this.errores = { ...this.errores, ...erroresValidacion };
                console.log("Errores en esquemaArticulos: ", this.errores);
            }

            if (this.tipoAccion != 2 && this.agregarStock === 'si') {
                try {
                    await esquemaInventario.validate(this.datosFormularioInventario, { abortEarly: false });
                    console.log("Validación de esquemaInventario exitosa");
                } catch (error) {
                    validacionInventarioExitosa = false;
                    const erroresValidacion = {};
                    error.inner.forEach((e) => {
                        erroresValidacion[e.path] = e.message;
                    });
                    this.errores = { ...this.errores, ...erroresValidacion };
                    console.log("Errores en esquemaInventario: ", this.errores);
                }
            }

            if (this.tipoAccion == 2 && validacionExitosa) {
                // Actualización del artículo
                try {
                    this.datosFormulario.fotografia = this.fotografia;
                    await this.actualizarArticulo(this.datosFormulario);
                    console.log("Actualización de artículo exitosa");
                } catch (error) {
                    console.error("Error al actualizar el artículo: ", error);
                }
            } else if (validacionExitosa || validacionInventarioExitosa) {
                // Registro del artículo
                this.datosFormulario.fotografia = this.fotografia;
                if (this.tipo_stock == "paquetes") {
                    this.datosFormulario.stock = this.datosFormulario.unidad_envase * this.datosFormulario.stock;
                    console.log("paquetes ",this.datosFormulario.stock)
                }

                try {
                    await this.registrarArticulo(this.datosFormulario);
                    console.log("Registro de artículo exitoso",this.datosFormulario);
                } catch (error) {
                    console.error("Error al registrar el artículo: ", error);
                }
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

        abrirModalImportar() {
            this.modalImportar = 1;
        },
        cerrarModalImportar() {
            this.modalImportar = 0;
            this.listarArticulo(1, '', 'nombre');
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
        createPaginationObject() {
            return {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            }
        },
        calcularPrecioCostoUnid() {
            if (this.datosFormulario.unidad_envase && this.datosFormulario.precio_costo_paq) {
                this.datosFormulario.precio_costo_unid = this.datosFormulario.precio_costo_paq / this.datosFormulario.unidad_envase;
                this.datosFormulario.precio_costo_unidVacio = false;
            }
        },
        calcularPrecioCostoPaq() {
            if (this.datosFormulario.unidad_envase && this.datosFormulario.precio_costo_unid) {
                this.datosFormulario.precio_costo_paq = this.datosFormulario.precio_costo_unid * this.datosFormulario.unidad_envase;
                this.datosFormulario.precio_costo_paqVacio = false;
            }
        },
        calcularPrecioP(precio_costo_unid, porcentage) {
            const margenG = precio_costo_unid * (porcentage / 100);
            const precioP = parseFloat(precio_costo_unid) + parseFloat(margenG);
            return precioP.toFixed(2);
        },
        //-------------hasta qui calcular -----------
        seleccionar(selected) {
            if (this.tituloModal2 == "Marcas") {
                this.marcaseleccionadaVacio = false;
                if (selected.condicion == 1) {
                    this.marcaseleccionada = selected;
                    this.validarCampo("idmarca");

                } else if (selected.condicion == 0) {
                    this.advertenciaInactiva('Marcas');
                }
            } else if (this.tituloModal2 == "Industrias") {
                this.industriaseleccionadaVacio = false;
                if (selected.estado == 1) {
                    this.industriaseleccionada = selected;
                    this.validarCampo("idindustria");

                } else if (selected.estado == 0) {
                    this.advertenciaInactiva('Industrias');
                }
            } else if (this.tituloModal2 == "Lineas") {
                if (selected.condicion == 1) {
                    this.lineaseleccionada = selected;
                    this.validarCampo("idcategoria");

                } else if (selected.condicion == 0) {
                    this.advertenciaInactiva('Lineas');
                }
            } else if (this.tituloModal2 == "Proveedors") {
                this.proveedorseleccionada = selected;
                this.validarCampo("idproveedor");

            } else if (this.tituloModal2 == "Grupos") {
                this.gruposeleccionada = selected;
                this.validarCampo("idgrupo");

            }

            this.arrayBuscador = [];
            this.modal2 = false;
        },

        seleccionar2(selected) {
            if (this.tituloModal6 == "Medidas") {
                this.medidaseleccionadaVacio = false;
                if (selected.estado == 1) {
                    this.medidaseleccionada = selected;
                    this.validarCampo("idmedida");

                } else if (selected.estado == 0) {
                    this.advertenciaInactiva('Medidas');
                }
            }
            this.arrayBuscador = [];
            this.modal6 = false;
        },


        listarIndustria(page, buscar, criterio) {
            let me = this;
            var url = '/industria?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayBuscador = respuesta.industrias.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        listarLinea(page, buscar, criterio) {
            let me = this;
            var url = '/categoria?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayBuscador = respuesta.categorias.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        listarMedida(page, buscar, criterio) {
            let me = this;
            var url = '/medida?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayBuscador = respuesta.medidas.data;
                me.paginationMedida = respuesta.paginationMedida;
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
        abrirModal2(titulo) {
            if (titulo == "Marcas") {

                this.listarMarca(1, '', 'nombre');

                this.modal2 = true;

                this.tituloModal2 = titulo;
                this.marcaseleccionadaVacio = false;
            } else if (titulo == "Industrias") {
                this.listarIndustria(1, '', 'nombre');
                this.modal2 = true;
                this.tituloModal2 = titulo;
                this.industriaseleccionadaVacio = false;

            }
            else if (titulo == "Lineas") {
                this.listarLinea(1, '', 'nombreLinea');
                this.modal2 = true;
                this.tituloModal2 = titulo;
                this.lineaseleccionadaVacio = false;

            } else if (titulo == "Proveedors") {
                this.listarproveedor(1, '', 'nombre');
                this.modal2 = true;
                this.tituloModal2 = titulo;
                this.proveedorseleccionadaVacio = false;

            } else if (titulo == "Grupos") {
                this.listargrupo(1, '', 'nombre_grupo');
                this.modal2 = true;
                this.tituloModal2 = titulo;
                this.gruposeleccionadaVacio = false;
            }

        },

        abrirModal6(titulo) {
            if (titulo == "Medidas") {
                this.listarMedida(1, '', 'descripcion_medida');
                this.modal6 = true;
                this.tituloModal6 = titulo;
                this.medidaseleccionadaVacio = false;
            }
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

        listarMarca(page, buscar, criterio) {
            let me = this;
            var url = '/marca?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;

            axios.get(url).then(function (response) {

                var respuesta = response.data;

                me.arrayBuscador = respuesta.marcas.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        //------listado proveedor, Registro Proveedor y editar-----------
        listarproveedor(page, buscar, criterio) {
            let me = this;
            var url = '/proveedor?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayBuscador = respuesta.personas.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },
        registrarProveedor() {
            // if (this.validarPersona()){
            //     return;
            // }

            let me = this;

            axios.post('/proveedor/registrar', {
                'nombre': this.nombre,
                'tipo_documento': this.tipo_documento,
                'num_documento': this.num_documento,
                'direccion': this.direccion,
                'telefono': this.telefono,
                'email': this.email,
                'contacto': this.contacto,
                'telefono_contacto': this.telefono_contacto,

            }).then(function (response) {
                me.cerrarModal3();
                me.listarproveedor(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        actualizarProveedor() {
            // if (this.validarPersona()){
            //         return;
            // }

            let me = this;

            axios.put('/proveedor/actualizar', {
                'id': this.proveedor_id,
                'nombre': this.nombre,
                'tipo_documento': this.tipo_documento,
                'num_documento': this.num_documento,
                'direccion': this.direccion,
                'telefono': this.telefono,
                'email': this.email,
                'contacto': this.contacto,
                'telefono_contacto': this.telefono_contacto,
            }).then(function (response) {
                me.cerrarModal3();
                me.listarproveedor(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //--------------------hasta aqui proveedor--------
        //--grupo listado ,registro y actualizar
        listargrupo(page, buscar, criterio) {
            let me = this;
            var url = '/grupos?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio;
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayBuscador = respuesta.grupos.data;
                me.pagination = respuesta.pagination;
            })
                .catch(function (error) {
                    console.log('ERRORES', error);
                });
        },
        registrarGrupo() {

            let me = this;

            axios.post('/grupos/registrar', {
                'nombre_grupo': this.nombre_grupo,
            }).then(function (response) {
                me.cerrarModal3();
                me.listargrupo(1, '', 'nombre_grupo');
            }).catch(function (error) {
                console.log(error);
            });
        },
        actualizarGrupo() {
            // if (this.validarPersona()){
            //         return;
            // }               
            let me = this;

            axios.put('/grupos/actualizar', {
                'id': this.grupo_id,
                'nombre_grupo': this.nombre_grupo,
            }).then(function (response) {
                me.cerrarModal3();
                me.listargrupo(1, '', 'nombre_grupo');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //----listar precio 4_julio-------
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
        //---------hasta aqui----------------
        cargarPdf() {
            window.open('/articulo/listarPdf', '_blank');
        },

        selectMedida() {
            let me = this;
            var url = '/medida/selectMedida';
            axios.get(url).then(function (response) {
                var respuesta = response.data;
                me.arrayMedida = respuesta.medidas;
            })
                .catch(function (error) {
                    console.log(error);
                });
        },

        cambiarPagina(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            //Envia la petición para visualizar la data de esa página
            me.listarArticulo(page, buscar, criterio);
        },
        cambiarPaginaMedida(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.paginationMedida.current_page = page;
            me.listarMedida(page, buscar, criterio);
            //Envia la petición para visualizar la data de esa página
        },
        cambiarPaginaMarca(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            me.listarMarca(page, buscar, criterio);
            //Envia la petición para visualizar la data de esa página
        },
        cambiarPaginaLinea(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            me.listarLinea(page, buscar, criterio);
            //Envia la petición para visualizar la data de esa página
        },
        cambiarPaginaIndustria(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            me.listarIndustria(page, buscar, criterio);
            //Envia la petición para visualizar la data de esa página
        },
        cambiarPaginaProveedor(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            me.listarproveedor(page, buscar, criterio);
            //Envia la petición para visualizar la data de esa página
        },
        cambiarPaginaGrupo(page, buscar, criterio) {
            let me = this;
            //Actualiza la página actual
            me.pagination.current_page = page;
            me.listargrupo(page, buscar, criterio);
            //Envia la petición para visualizar la data de esa página
        },
        //mostrar foto de articulo
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
        calcularPrecioValorMoneda(precio) {
            return ((precio * parseFloat(this.monedaPrincipal)).toFixed(2))
        },
        convertDolar(precio) {
            return (precio / parseFloat(this.monedaPrincipal))
        },
        registrarArticulo(data) {
            let me = this;
            var formulario = new FormData();
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    formulario.append(key, data[key]);
                }
            }

            axios.post('/articulo/registrar', formulario, {
                headers: {
                    'Content-Type': 'multipart/form-data' 
                }
            }).then(function (response) {
                var respuesta = response.data;
                me.idarticulo = respuesta.idArticulo;
                console.log("respuesta = ", me.idarticulo)
                me.cerrarModal();
                me.listarArticulo(1, '', 'nombre');
                me.toastSuccess("Articulo registrado correctamente");

                if (me.agregarStock === 'si') {
                    let arrayArticulos = [
                        {
                            idarticulo: me.idarticulo,
                            idalmacen: me.AlmacenSeleccionado,
                            cantidad: me.unidadStock,
                            fecha_vencimiento: me.fechaVencimientoAlmacen
                        }
                    ];
                    return axios.post('/inventarios/registrar', { inventarios: arrayArticulos });
                }
            }).then(function (response) {
                if (response) {
                    console.log(response.data);
                }
            }).catch(function (error) {
                console.error(error);
                me.toastError("Hubo un error al registrar el articulo o inventario");
            });
        },

        //---actuslizar articulo
        actualizarArticulo(data) {
            var formulario = new FormData();
            let me = this;
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    formulario.append(key, data[key]);
                }
            }


            axios.post('/articulo/actualizar', formulario, {
                    headers: {
                        'Content-Type': 'multipart/form-data' 
                    }
                }).then(function (response) {
                //alert("Datos actualizados con éxito");
                //console.log("datos actuales",formData);
                var respuesta = response.data;
                console.log("respuesta = ",respuesta)
                console.log("foto ",data)
                me.cerrarModal();
                me.listarArticulo(1, '', 'nombre');
                me.toastSuccess("Articulo actualizado correctamente")
            }).catch(function (error) {
                console.log(error);
                me.toastError("No se puedo actualizar el articulo")
            });
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
        advertenciaInactiva(nombre) {
            swal({
                title: 'Opción Inactiva',
                text: 'No puede seleccionar esta opción porque está inactiva.',
                type: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar',
                confirmButtonClass: 'btn btn-success',
                buttonsStyling: false,
            }).then(() => {
                if (nombre == 'Medidas') {
                    this.abrirModal6(nombre);
                }
                else {
                    this.abrirModal2(nombre);
                }

            });
        },
        //#################registro industria############
        registrarIndustria() {
            if (this.validarIndustria()) {
                return;
            }
            let me = this;

            axios.post('/industria/registrar', {
                'nombre': this.nombre,
                'estado': this.estado
            }).then(function (response) {
                me.cerrarModal3();
                //me.modal3=0;
                me.listarIndustria(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //#################hasta aqui####################
        //#################Actualizar Industria####################
        actualizarIndustria() {
            if (this.validarIndustria()) {
                return;
            }

            let me = this;

            axios.put('/industria/actualizar', {
                'nombre': this.nombre,
                'estado': this.estado,
                'id': this.industria_id
            }).then(function (response) {
                me.cerrarModal3();
                me.listarIndustria(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //#################hasta aqui####################
        //#################registro industria############
        registrarMarca() {
            if (this.validarIndustria()) {
                return;
            }
            let me = this;

            axios.post('/marca/registrar', {
                'nombre': this.nombre,
                'condicion': this.condicion
            }).then(function (response) {
                me.cerrarModal3();
                //me.modal3=0;
                me.listarMarca(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //#################hasta aqui####################
        //#################-Actualizar Industria-####################
        actualizarMarca() {
            if (this.validarIndustria()) {
                return;
            }
            let me = this;

            axios.put('/marca/actualizar', {
                'nombre': this.nombre,
                'condicion': this.condicion,
                'id': this.marca_id
            }).then(function (response) {
                me.cerrarModal3();
                me.listarMarca(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //#################hasta aqui####################
        //##############registrar linea##########
        registrarLinea() {
            if (this.validarIndustria()) {
                return;
            }
            let me = this;

            axios.post('/categoria/registrar', {
                'nombre': this.nombreLinea,
                'condicion': this.condicion,
                'descripcion': this.descripcion,
                'codigoProductoSin': this.codigoProductoSin
            }).then(function (response) {
                me.cerrarModal3();
                //me.modal3=0;
                me.listarLinea(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //#################hasta aqui####################
        actualizarLinea() {
            if (this.validarIndustria()) {
                return;
            }
            let me = this;

            axios.put('/categoria/actualizar', {
                'nombre': this.nombreLinea,
                'condicion': this.condicion,
                'descripcion': this.descripcion,
                'codigoProductoSin': this.codigoProductoSin,
                'id': this.linea_id
            }).then(function (response) {
                me.cerrarModal3();
                me.listarLinea(1, '', 'nombre');
            }).catch(function (error) {
                console.log(error);
            });
        },

        //#################registro medida############
        registrarMedida() {
            if (this.validarMedida()) {
                return;
            }
            let me = this;

            axios.post('/medida/registrar', {
                'descripcion_medida': this.descripcion_medida,
                'descripcion_corta': this.descripcion_corta,
                'estado': this.estado
            }).then(function (response) {
                me.cerrarModal7();
                me.listarMedida(1, '', 'descripcion_medida');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //#################hasta aqui####################
        actualizarMedida() {
            if (this.validarMedida()) {
                return;
            }

            let me = this;

            axios.put('/medida/actualizar', {
                'descripcion_medida': this.descripcion_medida,
                'descripcion_corta': this.descripcion_corta,
                'estado': this.estado,
                'id': this.medida_id
            }).then(function (response) {
                me.cerrarModal7();
                me.listarMedida(1, '', 'descripcion_medida');
            }).catch(function (error) {
                console.log(error);
            });
        },
        //#################hasta aqui####################
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
            this.modal = 0;
            this.tituloModal = '';
           
            //validacion para quitar borde rojo en los inputs
            this.codigoVacio = false;
            this.nombreProductoVacio = false;
            this.unidad_envaseVacio = false;
            this.nombre_genericoVacio = false;
            this.precio_costo_unidVacio = false;
            this.precio_costo_paqVacio = false;
            this.precio_ventaVacio = false;
            this.costo_compraVacio = false;
            this.stockVacio = false;
            this.descripcionVacio = false;
            this.fotografiaVacio = false;
            this.lineaseleccionadaVacio = false;
            this.marcaseleccionadaVacio = false;
            this.industriaseleccionadaVacio = false;
            this.proveedorseleccionadaVacio = false;
            this.gruposeleccionadaVacio = false;
            this.medidaseleccionadaVacio = false;
            //
            this.codigo = '';
            this.nombre_producto = '';
            this.nombre_generico = '';
            this.precio_venta = 0;
            this.precio_costo_unid = 0;
            this.precio_costo_paq = 0;
            this.stock = 5;
            this.descripcion = '';
            this.fotografia = ''; //Pasando el valor limpio de la referencia
            this.fotoMuestra = null;
            this.lineaseleccionada.nombre = '';
            this.marcaseleccionada.nombre = '';
            this.industriaseleccionada.nombre = '';
            this.proveedorseleccionada.nombre = '';
            this.gruposeleccionada.nombre_grupo = '';
            this.medidaseleccionada.descripcion_medida = '';
            this.errorArticulo = 0;

            this.idmedida = 0;
            this.costo_compra = 0;

            this.precio_uno = 0;
            this.precio_dos = 0;
            this.precio_tres = 0;
            this.precio_cuatro = 0;
        },
        abrirModal(modelo, accion, data = []) {
            switch (modelo) {
                case "articulo":
                    {
                        switch (accion) {

                            case 'registrar':
                                {
                                    this.modal = 1;
                                    this.tituloModal = 'Registrar Artículo';

                                    this.tipoAccion = 1;
                                    this.fotografia = '';

                                    this.datosFormulario = {
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
                                        idmedida: null
                                    };
                                    this.errores = {};
                                    break;
                                }
                            case 'actualizar':
                                {
                                    this.modal = 1;
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
                                    this.industriaseleccionada = { nombre: data['nombre_industria'], id: data['idindustria'] };
                                    //this.lineaseleccionada = {nombre: data['nombre_categoria']};
                                    this.lineaseleccionada = { nombre: data['nombre_categoria'], id: data['idcategoria'] };
                                    //this.marcaseleccionada = {nombre: data['nombre_marca']};
                                    this.marcaseleccionada = { nombre: data['nombre_marca'], id: data['idmarca'] };
                                    this.proveedorseleccionada = { nombre: data['nombre_proveedor'], id: data['idproveedor'] };
                                    //this.gruposeleccionada = {nombre_grupo: data['nombre_grupo']};
                                    this.gruposeleccionada = { nombre_grupo: data['nombre_grupo'], id: data['idgrupo'] };
                                    this.medidaseleccionada = { descripcion_medida: data['descripcion_medida'], id: data['idmedida'] };

                                    this.precio_uno = this.calcularPrecioValorMoneda(data['precio_uno']);
                                    this.precio_dos = this.calcularPrecioValorMoneda(data['precio_dos']);
                                    this.precio_tres = this.calcularPrecioValorMoneda(data['precio_tres']);
                                    this.precio_cuatro = this.calcularPrecioValorMoneda(data['precio_cuatro']);
                                    // this.precios.forEach((precio) => {
                                    //     this.calcularPrecio(precio);
                                    // });
                                    this.fechaVencimientoSeleccion = data['vencimiento'];
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

            if (index === 0) {
                this.precio_uno = precio_publico.toFixed(2);
            } else if (index === 1) {
                this.precio_dos = precio_publico.toFixed(2);
            } else if (index === 2) {
                this.precio_tres = precio_publico.toFixed(2);
            } else if (index === 3) {
                this.precio_cuatro = precio_publico.toFixed(2);
            }
        },

        cerrarModal3() {
            this.modal3 = 0;
            this.tituloModal3 = '';
            this.nombre = '';
            this.limpiarErrores();
        },
        cerrarModal6() {
            this.modal6 = 0;
            this.tituloModal6 = '';
            this.descripcion_medida = '';
            this.descripcion_corta = '';
        },
        cerrarModal7() {
            this.modal7 = 0;
            this.tituloModal7 = '';
            this.descripcion_medida = '';
            this.descripcion_corta = '';
            this.limpiarErrores2();
        },
        limpiarErrores() {
            if (this.tituloModal2 === 'Industrias') {
                this.errorMostrarMsjIndustria = [];
                this.errorIndustria = 0;
            } else if (this.tituloModal2 === 'Marcas') {
                this.errorMostrarMsjIndustria = [];
                this.errorIndustria = 0;
            } else {
                this.errorMostrarMsjIndustria = [];
                this.errorIndustria = 0;
            }
        },
        limpiarErrores2() {
            this.tituloModal6 === 'Medidas'
            this.errorMostrarMsjMedida = [];
            this.errorMedida = 0;
        },
        //################Validar registros de industrial########
        validarIndustria() {
            this.errorIndustria = 0;
            this.errorMostrarMsjIndustria = [];

            if (this.tituloModal2 === 'Industrias') {
                if (!this.nombre) this.errorMostrarMsjIndustria.push("El nombre de Industria no puede estar vacío.");
            } else if (this.tituloModal2 === 'Marcas') {
                if (!this.nombre) this.errorMostrarMsjIndustria.push("El nombre de Marca no puede estar vacío.");
            } else if (this.tituloModal2 === 'Lineas') {
                if (!this.nombreLinea) this.errorMostrarMsjIndustria.push("El nombre de Linea no puede estar vacío.");
                if (!this.descripcion) this.errorMostrarMsjIndustria.push("La descripcion de Linea no puede estar vacío.");
                if (!this.codigoProductoSin) this.errorMostrarMsjIndustria.push("El codigo de Linea no puede estar vacío.");
            }

            //if (!this.nombre) this.errorMostrarMsjIndustria.push("El nombre de Industria no puede estar vacío.");
            if (this.errorMostrarMsjIndustria.length) this.errorIndustria = 1;

            return this.errorIndustria;
        },
        //################Validar registros de medida########
        validarMedida() {
            this.errorMedida = 0;
            this.errorMostrarMsjMedida = [];

            if (!this.descripcion_medida) this.errorMostrarMsjMedida.push("El nombre de la Medida no puede estar vacío.");
            if (this.errorMostrarMsjMedida.length) this.errorMedida = 1;

            return this.errorMedida;
        },

        //################placeholder mensaje si es indus►ria, marca o linea########
        placeholderInput(inputType) {
            if (this.tituloModal2 === 'Marcas') {
                return 'Nombre de Marca'
            } else if (this.tituloModal2 === 'Industrias') {
                return 'Nombre de Industria'
                // } else if (this.tituloModal2 === 'Proveedor') {
                //     return 'Nombre de Proveedor'
            } else if (this.tituloModal2 === 'Grupos') {
                return 'Nombre de Grupo'
            } else if (this.tituloModal2 === 'Lineas') {
                if (inputType === 'nombre') {
                    return 'Nombre de Linea';
                } else if (inputType === 'descripcion') {
                    return 'Descripcion de Linea';
                }
                else if (inputType === 'codigoProductoSin') {
                    return 'Codigo de Linea';
                }
            }
        },
        //############hasta aqui-#########
        //################-Abrl moda de industrial,marca,Linea########
        abrirModal3(modelo3, accion3, data = []) {
            switch (modelo3) {
                case "industria":
                    {
                        switch (accion3) {
                            case 'registrarInd':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Registrar Industria';
                                    this.nombre = '';
                                    this.estado = '';
                                    this.tipoAccion2 = 3;
                                    //this.modal3=true;
                                    break;
                                }
                            case 'actualizarInd':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Actualizar Industria';
                                    this.tipoAccion2 = 4;
                                    this.industria_id = data['id'];
                                    this.nombre = data['nombre'];
                                    this.estado = data['estado'];
                                    break;
                                }

                        }
                    }
                case "Marca":
                    {
                        switch (accion3) {
                            case 'registrarMar':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Registrar Marca';
                                    this.nombre = '';
                                    this.condicion = '';
                                    this.tipoAccion2 = 5;
                                    break;
                                }
                            case 'actualizar':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Actualizar marca';
                                    this.tipoAccion2 = 6;
                                    this.marca_id = data['id'];
                                    this.nombre = data['nombre'];
                                    this.condicion = data['condicion'];
                                    break;
                                }
                        }
                    }
                case "Linea":
                    {
                        switch (accion3) {
                            case 'registrarLin':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Registrar Linea';
                                    this.nombreLinea = '';
                                    this.descripcion = '';
                                    this.codigoProductoSin = 0;
                                    this.condicion = '';
                                    this.tipoAccion2 = 7;
                                    break;

                                }
                            case 'actualizarLin':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Actualizar Linea';
                                    this.tipoAccion2 = 8;
                                    this.linea_id = data['id'];
                                    this.nombreLinea = data['nombre'];
                                    this.descripcion = data['descripcion'];
                                    this.codigoProductoSin = data['codigoProductoSin'];
                                    this.condicion = data['condicion'];
                                    break;
                                }
                        }

                    }
                case "Grupo":
                    {
                        switch (accion3) {
                            case 'registrarGrupo':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Registrar Grupo';
                                    this.tipoAccion2 = 11;
                                    this.nombre_grupo = '';

                                    break;
                                }
                            case 'actualizarGrupo':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Actualizar Grupo';
                                    this.tipoAccion2 = 12;
                                    this.grupo_id = data['id'];
                                    this.nombre_grupo = data['nombre_grupo'];
                                    break;
                                }
                        }
                    }
                case "Proveedor":
                    {
                        switch (accion3) {
                            case 'registrarProv':
                                {
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Registrar Provedor';
                                    this.nombre = '';
                                    this.tipo_documento = 'RUC';
                                    this.num_documento = '';
                                    this.direccion = '';
                                    this.telefono = '';
                                    this.email = '';
                                    this.contacto = '';
                                    this.telefono_contacto = '';
                                    this.tipoAccion2 = 9;
                                    break;
                                }
                            case 'actualizarProv':
                                {
                                    //console.log('Proveedor',data)
                                    this.modal3 = 1;
                                    this.tituloModal3 = 'Actualizar Provedor';
                                    this.tipoAccion2 = 10;
                                    this.proveedor_id = data['id'];
                                    this.nombre = data['nombre'];
                                    this.tipo_documento = data['tipo_documento'];
                                    this.num_documento = data['num_documento'];
                                    this.direccion = data['direccion'];
                                    this.telefono = data['telefono'];
                                    this.email = data['email'];
                                    this.contacto = data['contacto'];
                                    this.telefono_contacto = data['telefono_contacto'];
                                    break;
                                }
                        }
                    }
            }
        },
        //############3hasta aqui######################

        //############3hasta aqui######################
        abrirModal7(modelo6, accion6, data = []) {
            switch (modelo6) {
                case "medida":
                    {
                        switch (accion6) {
                            case 'registrarMed':
                                {
                                    this.modal7 = 1;
                                    this.tituloModal7 = 'Registrar Medida';
                                    this.descripcion_medida = '';
                                    this.descripcion_corta = '';
                                    this.estado = '1';
                                    this.tipoAccion6 = 6;
                                    break;
                                }
                            case 'actualizarMed':
                                {
                                    this.modal7 = 1;
                                    this.tituloModal7 = 'Actualizar Medida';
                                    this.tipoAccion6 = 7;
                                    this.medida_id = data['id'];
                                    this.descripcion_medida = data['descripcion_medida'];
                                    this.descripcion_corta = data['descripcion_corta'];
                                    this.estado = data['estado'];
                                    break;
                                }

                        }
                    }
            }
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
        recuperarIdRol() {
            this.rolUsuario = window.userData.rol;
        },

    },
    mounted() {
        this.selectAlmacen();
        this.recuperarIdRol();
        this.datosConfiguracion();
        this.obtenerConfiguracionTrabajo();
        this.listarArticulo(1, this.buscar, this.criterio);
        this.listarPrecio();//aumenTe 6julio
    }
}
</script>
<style>
.card-error {
    margin-bottom: 10px;
    width: 100%;
    padding: 15px;
    border-radius: 15px;
    border: 2px solid #ab7078;
    background-color: #fec0ca;

}

.csv-headers-container {
    margin-top: 10px;
}

.csv-headers-list {
    list-style-type: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
}

.csv-header {
    background-color: #3498db;
    color: white;
    padding: 5px 10px;
    margin: 5px;
    border-radius: 5px;
    cursor: pointer;
}

.csv-header label {
    display: flex;
    align-items: center;
}

.csv-header input {
    margin-right: 5px;
}

.selected-headers-container {
    margin-top: 10px;
}

.selected-headers-list {
    list-style-type: none;
    padding: 10px;
    display: flex;
    flex-wrap: wrap;
}

/* .selected-header {
        box-shadow: 10px 5px 5px black;
        color: black;

        -webkit-box-shadow: 3px -7px 38px -1px rgba(27, 209, 11, 0.69);
        -moz-box-shadow: 3px -7px 38px -1px rgba(27, 209, 11, 0.69);
        box-shadow: 3px -7px 38px -1px rgba(27, 209, 11, 0.69);
    } */

.modal-content {
    width: 100% !important;
    position: absolute !important;
}

.mostrar {

    display: list-item !important;
    opacity: 1 !important;
    position: absolute !important;
    background-color: #3c29297a !important;
}

.div-error {
    display: flex;
    justify-content: center;
}

.text-error {
    color: red !important;
    font-weight: bold;
}

.table-responsive {
    overflow-x: auto;
}


.sticky-column {
    position: sticky;
    left: 0;
    z-index: 1;
    background-color: white;
}

.border-red {
    border-color: red !important;
}
</style>
