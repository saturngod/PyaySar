import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\UserPreferenceController::edit
* @see app/Http/Controllers/UserPreferenceController.php:13
* @route '/settings/preference'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/preference',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UserPreferenceController::edit
* @see app/Http/Controllers/UserPreferenceController.php:13
* @route '/settings/preference'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserPreferenceController::edit
* @see app/Http/Controllers/UserPreferenceController.php:13
* @route '/settings/preference'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserPreferenceController::edit
* @see app/Http/Controllers/UserPreferenceController.php:13
* @route '/settings/preference'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\UserPreferenceController::edit
* @see app/Http/Controllers/UserPreferenceController.php:13
* @route '/settings/preference'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserPreferenceController::edit
* @see app/Http/Controllers/UserPreferenceController.php:13
* @route '/settings/preference'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserPreferenceController::edit
* @see app/Http/Controllers/UserPreferenceController.php:13
* @route '/settings/preference'
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
* @see \App\Http\Controllers\UserPreferenceController::update
* @see app/Http/Controllers/UserPreferenceController.php:22
* @route '/settings/preference'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/settings/preference',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UserPreferenceController::update
* @see app/Http/Controllers/UserPreferenceController.php:22
* @route '/settings/preference'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserPreferenceController::update
* @see app/Http/Controllers/UserPreferenceController.php:22
* @route '/settings/preference'
*/
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\UserPreferenceController::update
* @see app/Http/Controllers/UserPreferenceController.php:22
* @route '/settings/preference'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\UserPreferenceController::update
* @see app/Http/Controllers/UserPreferenceController.php:22
* @route '/settings/preference'
*/
updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

update.form = updateForm

const UserPreferenceController = { edit, update }

export default UserPreferenceController