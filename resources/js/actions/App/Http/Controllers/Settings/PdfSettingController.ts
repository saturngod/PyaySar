import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\PdfSettingController::edit
* @see app/Http/Controllers/Settings/PdfSettingController.php:16
* @route '/settings/pdf'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::edit
* @see app/Http/Controllers/Settings/PdfSettingController.php:16
* @route '/settings/pdf'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::edit
* @see app/Http/Controllers/Settings/PdfSettingController.php:16
* @route '/settings/pdf'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::edit
* @see app/Http/Controllers/Settings/PdfSettingController.php:16
* @route '/settings/pdf'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::edit
* @see app/Http/Controllers/Settings/PdfSettingController.php:16
* @route '/settings/pdf'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::edit
* @see app/Http/Controllers/Settings/PdfSettingController.php:16
* @route '/settings/pdf'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::edit
* @see app/Http/Controllers/Settings/PdfSettingController.php:16
* @route '/settings/pdf'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::update
* @see app/Http/Controllers/Settings/PdfSettingController.php:39
* @route '/settings/pdf'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/settings/pdf',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::update
* @see app/Http/Controllers/Settings/PdfSettingController.php:39
* @route '/settings/pdf'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::update
* @see app/Http/Controllers/Settings/PdfSettingController.php:39
* @route '/settings/pdf'
*/
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::update
* @see app/Http/Controllers/Settings/PdfSettingController.php:39
* @route '/settings/pdf'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\PdfSettingController::update
* @see app/Http/Controllers/Settings/PdfSettingController.php:39
* @route '/settings/pdf'
*/
updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

update.form = updateForm

const PdfSettingController = { edit, update }

export default PdfSettingController