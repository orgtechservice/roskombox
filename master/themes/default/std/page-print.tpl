<html>
 <head>
  <title>{
   IF defined(TITLE) TITLE ELSE
     IF defined(project) project.title ELSE "ООО Оргтехсервис" ENDIF
   ENDIF
   }</title>
  {FOREACH HTTP as meta}
  <meta http-equiv="{escape(meta.name)}" content="{escape(meta.value)}" />
  {ENDEACH}
  {FOREACH META as meta}
  <meta name="{escape(meta.name)}" content="{escape(meta.value)}" />
  {ENDEACH}
  {FOREACH LINKS as link}
  <link rel="{escape(link.rel)}" href="{escape(link.URL)}" />
  {ENDEACH}
  <link href="{INFO.SKINDIR}/print.css" type="text/css" rel="stylesheet" />
 </head>
 <body>
  <div class="print-content">
   {INCLUDE CONTENT}
  </div>
 </body>
</html>
