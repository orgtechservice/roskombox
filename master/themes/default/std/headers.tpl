{ if LAYOUT = 'layout/voffice' }
	<script src="{INFO.PREFIX}static/voffice/jquery/jquery-2.1.4.min.js" language="JavaScript" type="text/javascript"></script>
	<script type="text/javascript" language="JavaScript" src="{INFO.PREFIX}static/voffice/momentjs/moment-with-locales.min.js"></script>
	<script type="text/javascript" language="JavaScript" src="{INFO.PREFIX}static/voffice/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
	<link rel="stylesheet" href="{INFO.PREFIX}static/voffice/bootstrap/bootstrap.css">
	<link rel="stylesheet" href="{INFO.PREFIX}static/voffice/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css">
	<link rel="stylesheet" href="{INFO.PREFIX}static/voffice/voffice.css">
	<script src="{INFO.PREFIX}static/voffice/bootstrap/bootstrap.min.js"></script>
	<script src="{INFO.PREFIX}static/voffice/bootstrap-checkbox/bootstrap-checkbox.js"></script>
	<script src="{INFO.PREFIX}static/voffice/bootstrap-select/bootstrap-select.min.js"></script>
	<script src="{INFO.PREFIX}static/voffice/bootstrap-select/defaults-ru_RU.min.js"></script>
	<link rel="stylesheet" href="{INFO.PREFIX}static/voffice/bootstrap-select/bootstrap-select.min.css">
	<script src="{INFO.PREFIX}static/voffice/voffice.js"></script>
{ elseif LAYOUT = 'layout/estore' }
      <!-- Стили -->
      <link href="{INFO.PREFIX}static/estore/bootstrap.min.css" rel="stylesheet">
      <link href="{INFO.PREFIX}static/estore/font-awesome.min.css" rel="stylesheet">
      <link href="{INFO.PREFIX}static/estore/dashboard.css" rel="stylesheet">
      <link href="{INFO.PREFIX}static/estore/js-libs-dev/note.css" rel="stylesheet">

      <!-- Javascript библиотеки -->
      <!-- <script src="{INFO.PREFIX}static/estore/js-libs/jquery.min.js"></script> -->
      <script src="{INFO.PREFIX}static/estore/js-libs/jquery-2.1.1.min.js"></script>
      <script src="{INFO.PREFIX}static/estore/js-libs/underscore-min.js"></script>
      <script src="{INFO.PREFIX}static/estore/js-libs/backbone-min.js"></script>
      <script src="{INFO.PREFIX}static/estore/js-libs/bootstrap.min.js"></script>
      <script src="{INFO.PREFIX}static/estore/js-libs-dev/note.js"></script>

      <!-- momentjs и datetimepicker для красивого календаря (уже есть в вирт.офисе) -->
      <script type="text/javascript" src="{INFO.PREFIX}static/voffice/momentjs/moment-with-locales.min.js"></script>
      <script type="text/javascript" src="{INFO.PREFIX}static/voffice/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
      <link rel="stylesheet" href="{INFO.PREFIX}static/voffice/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css">
{ else }
	<link href="{INFO.SKINDIR}/main.css?v=24" type="text/css" rel="stylesheet" />
	<link href="{INFO.PREFIX}static/css/gcore.css" type="text/css" rel="stylesheet" />
	<link href="{INFO.SKINDIR}/gcmain.css" type="text/css" rel="stylesheet" />
	<!-- <script type="text/javascript" src="{INFO.PREFIX}static/js/calendar.js"></script> -->

	{ifdef SESSION.user}
		<!-- скрипты вирт.офиса здесь нужны, чтобы работали уведомления по всей админке -->
		<script src="{INFO.PREFIX}static/voffice/jquery/jquery-2.1.4.min.js" language="JavaScript" type="text/javascript"></script>
		<script src="{INFO.PREFIX}static/voffice/voffice.js"></script>
	{endif}
{ endif }
