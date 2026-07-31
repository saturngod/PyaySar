import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::edit
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:13
* @route '/settings/invoice'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/invoice',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::edit
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:13
* @route '/settings/invoice'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::edit
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:13
* @route '/settings/invoice'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::edit
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:13
* @route '/settings/invoice'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::edit
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:13
* @route '/settings/invoice'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::edit
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:13
* @route '/settings/invoice'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::edit
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:13
* @route '/settings/invoice'
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
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::update
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:22
* @route '/settings/invoice'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/settings/invoice',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::update
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:22
* @route '/settings/invoice'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::update
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:22
* @route '/settings/invoice'
*/
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::update
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:22
* @route '/settings/invoice'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\InvoiceSettingsController::update
* @see app/Http/Controllers/Settings/InvoiceSettingsController.php:22
* @route '/settings/invoice'
*/
updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

update.form = updateForm

const InvoiceSettingsController = { edit, update }

export default InvoiceSettingsController