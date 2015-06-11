request /es
===========

La petición la recibe indexAction del servicio edemy.main.

Para comprobar si la respuesta ha caducado obtenemos $lastmodified de la ruta.

    ))) [ruta]_lastmodified
        el listener calcula el lastmodified de la ruta que se muestra
        o de las templates que intervienen (layout/theme.html.twig)
        onFrontpageLastmodified
            ))) [frontpage_route]_lastmodified
                on[FrontpageRoute]Lastmodified
            $lastmodified_files = $this->getLastModifiedFiles('*.html.twig');

-> Si no ha habido modificación devuelve respuesta 304.
    
    if ($response = $this->ifNotModified304($lastmodified)) {
        return $response;
    }

En caso contrario se genera el contenido principal de la respuesta.

    ))) edemy_content
        onContent
        ))) edemy_precontent_module
        ))) [route]
        ))) edemy_postcontent_module
    
-> Si es stopPropagation lo se devuelve una respuesta con ese contenido.

En caso contrario se obtienen los demás elementos que aparecen asociados a la ruta.

-> Se devuelve la respuesta completa.




