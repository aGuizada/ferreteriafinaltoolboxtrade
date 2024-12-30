<div class="sidebar">
    <button class="sidebar-minimizer brand-minimizer" type="button" style="background-color: #a4b7c1"></button>
    <nav class="sidebar-nav">
        <ul class="nav">

            <li @click="menu=5" class="nav-item">
                <a class="nav-link active" href="#"><i class="fa fa-dashboard"></i> ESCRITORIO</a>
            </li>
            <li class="nav-title">
                OPERACIONES
            </li>


            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-briefcase"></i> EMPRESA</a>
                <ul class="nav-dropdown-items">
                    <li @click="menu=13" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-building" style="font-size: 19px;"></i> EMPRESA</a>
                    </li>
                    <li @click="menu=14" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-sitemap" style="font-size: 19px;"></i>
                            SUCURSALES</a>
                    </li>
                    <!--<li @click="menu=41" class="nav-item">
                        <a class="nav-link" href="#"><i class="icon-list" style="font-size: 11px;"></i> PUNTOS DE
                            VENTA</a>
                    </li> -->

                </ul>
            </li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-usd"></i>
                    FINANZAS</a>
                <ul class="nav-dropdown-items">
                    <li @click="menu=16" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-money"></i> APERTURA/CIERRE CAJA</a>
                    </li>
                    <!--
                    <li @click="menu=65" class="nav-item">
                        <a class="nav-link" href="#"><i class="icon-list" style="font-size: 11px;"></i> BANCOS</a>
                    </li>-->

                    <!--  <li @click="menu=15" class="nav-item">
    <a class="nav-link" href="#"><i class="fas fa-money-bill-alt"></i> MONEDA</a>
</li>-->
                </ul>
            </li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-shopping-cart" aria-hidden="true"></i>
                    VENTAS</a>
                <ul class="nav-dropdown-items">
                    <li @click="menu=0" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-shopping-cart"></i> VENTAS</a>
                    <li @click="menu=55" class="nav-item">
                        <!-- <li @click="menu=40" class="nav-item">
                        <a class="nav-link" href="#"><i class="icon-list" style="font-size: 11px;"></i> REGISTRO
                            VENTAS</a>
                    </li> -->
                    <!-- <li @click="menu=53" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-credit-card" style="font-size: 16px;"></i> VENTAS A
                            CREDITO</a>
                    </li> -->
                    <li @click="menu=6" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-users" style="font-size: 16px;"></i> CLIENTES</a>
                    </li>
                    <li @click="menu=23" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-file-text-o" style="font-size: 16px;"></i>
                            COTIZACIONES</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#">
                    <i class="fa fa-shopping-bag" aria-hidden="true"></i> COMPRAS
                </a>
                <ul class="nav-dropdown-items">
                    <li @click="menu=3" class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa fa-money" style="font-size: 19px;"></i> INGRESOS
                        </a>
                    </li>
                    <li @click="menu=4" class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa fa-users" style="font-size: 19px;"></i> PROVEEDORES
                        </a>
                    </li>
                    <!-- <li @click="menu=22" class="nav-item">
            <a class="nav-link" href="#">
                <i class="fa fa-clipboard" style="font-size: 11px;"></i> PEDIDOS A PROV.
            </a>
        </li> -->
                    <li @click="menu=70" class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa fa-plus-circle" style="font-size: 19px"></i> NUEVA COMPRA
                        </a>
                    </li>
                    <li @click="menu=72" class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa fa-credit-card" style="font-size: 19px"></i> COMPRAS A CREDITO
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-file-text"></i> ALMACÉN</a>
                <ul class="nav-dropdown-items">
                    <li @click="menu=24" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-building" style="font-size: 19px;"></i>
                            ALMACENES</a>
                    </li>
                    <li @click="menu=25" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-cubes" style="font-size: 19px;"></i> INVENTARIO</a>
                    </li>
                    <li @click="menu=28" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-line-chart" style="font-size: 19px;"></i> MONITOREO
                            PRODUCTOS</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-tags"></i> PRODUCTOS</a>
                <ul class="nav-dropdown-items">
                    <li @click="menu=2" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-product-hunt" style="font-size: 19px;"></i>
                            PRODUCTOS</a>
                    </li>

                    <li @click="menu=18" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-trademark" style="font-size: 19px;"></i> MARCA</a>
                    </li>
                    <li @click="menu=19" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-tags" style="font-size: 19px;"></i> LINEA</a>
                    </li>
                    <li @click="menu=20" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-industry" style="font-size: 19px;"></i>
                            INDUSTRIA</a>
                    </li>
                    <li @click="menu=27" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-arrows-alt" style="font-size: 19px;"></i>
                            MEDIDAS</a>
                    </li>
                </ul>
            </li>



            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#"><i class="fa fa-lock"></i> ACCESO</a>
                <ul class="nav-dropdown-items">
                    <li @click="menu=7" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-user" style="font-size: 19px;"></i> USUARIOS</a>
                    </li>
                    <li @click="menu=8" class="nav-item">
                        <a class="nav-link" href="#"><i class="fa fa-id-badge" style="font-size: 19px;"></i> ROLES</a>
                    </li>
                </ul>
            </li>
          
    </nav>

</div>