<style>
    html {
        font-size: 13px;
    }

    @media screen and (min-width: 1024px) {
        html {
            font-size: 14px;
        }
    }

    .app__wrapper {
        display: flex; flex-direction: column;
        background: #fff;
    }
    .app__main {
        flex-grow: 2;
    }
    .app__footer {
        margin-top: 1.5rem;
        padding: 1.5rem;
        box-shadow: 0 -2px 0 0 #f5f5f5;
    }
        
    .container {
        padding-right: 1.5rem;
        padding-left: 1.5rem;
    }
    .navbar {
        z-index: 10;
        margin-bottom: 3rem;
    }
    .navbar-item.router-link-active {
        border-bottom: 1px solid currentColor;
    }
    tr:first-child th {
        position: sticky;
        background-color: #fff;
        top: 0;
        z-index: 10;
    }
    th {
        white-space: nowrap;
    }
    .table td {
        vertical-align: middle;
    }
    a:not([class]) {
        text-decoration: underline;
    }
    .is-floating-in-label .label {
        pointer-events: none;
    }

    .opacity {
        opacity: .4;
    }
    .status-color {
        margin: -.13em .15em 0;
        display: inline-flex;
        width: 16px; height: 16px;
        vertical-align: middle;
        border-radius: 50%;
    }

</style>

<template>
<div class="app__wrapper">

    <link media="print" onload="this.media='all'" rel="stylesheet"
        href="//cdn.materialdesignicons.com/2.5.94/css/materialdesignicons.min.css"
    >

    <b-navbar shadow wrapper-class="container">

        <template slot="brand">
            <b-navbar-item tag="b">Shop Manager</b-navbar-item>
        </template>
        <template slot="start">
            <b-navbar-item tag="router-link" to="/list">
                Заказы
            </b-navbar-item>
            <b-navbar-item tag="router-link" to="/settings">
                Настройки
            </b-navbar-item>
        </template>

    </b-navbar>
	
	<div class="app__main">
		<router-view></router-view>
	</div>
	

	<footer class="footer app__footer">
		<div class="content has-text-centered">
			<p><a target="_blank" href="https://web-kirov.ru/">«Effect»</a></p>
		</div>
	</footer>


</div>
</template>

<script>
    export default {}
</script>