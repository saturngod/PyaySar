import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ItemController::index
* @see app/Http/Controllers/ItemController.php:16
* @route '/items'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ItemController::index
* @see app/Http/Controllers/ItemController.php:16
* @route '/items'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ItemController::index
* @see app/Http/Controllers/ItemController.php:16
* @route '/items'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::index
* @see app/Http/Controllers/ItemController.php:16
* @route '/items'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ItemController::index
* @see app/Http/Controllers/ItemController.php:16
* @route '/items'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::index
* @see app/Http/Controllers/ItemController.php:16
* @route '/items'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::index
* @see app/Http/Controllers/ItemController.php:16
* @route '/items'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\ItemController::create
* @see app/Http/Controllers/ItemController.php:28
* @route '/items/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/items/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ItemController::create
* @see app/Http/Controllers/ItemController.php:28
* @route '/items/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ItemController::create
* @see app/Http/Controllers/ItemController.php:28
* @route '/items/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::create
* @see app/Http/Controllers/ItemController.php:28
* @route '/items/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ItemController::create
* @see app/Http/Controllers/ItemController.php:28
* @route '/items/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::create
* @see app/Http/Controllers/ItemController.php:28
* @route '/items/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::create
* @see app/Http/Controllers/ItemController.php:28
* @route '/items/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\ItemController::store
* @see app/Http/Controllers/ItemController.php:36
* @route '/items'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/items',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ItemController::store
* @see app/Http/Controllers/ItemController.php:36
* @route '/items'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ItemController::store
* @see app/Http/Controllers/ItemController.php:36
* @route '/items'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ItemController::store
* @see app/Http/Controllers/ItemController.php:36
* @route '/items'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ItemController::store
* @see app/Http/Controllers/ItemController.php:36
* @route '/items'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\ItemController::show
* @see app/Http/Controllers/ItemController.php:46
* @route '/items/{item}'
*/
export const show = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/items/{item}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ItemController::show
* @see app/Http/Controllers/ItemController.php:46
* @route '/items/{item}'
*/
show.url = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { item: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            item: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return show.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ItemController::show
* @see app/Http/Controllers/ItemController.php:46
* @route '/items/{item}'
*/
show.get = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::show
* @see app/Http/Controllers/ItemController.php:46
* @route '/items/{item}'
*/
show.head = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ItemController::show
* @see app/Http/Controllers/ItemController.php:46
* @route '/items/{item}'
*/
const showForm = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::show
* @see app/Http/Controllers/ItemController.php:46
* @route '/items/{item}'
*/
showForm.get = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::show
* @see app/Http/Controllers/ItemController.php:46
* @route '/items/{item}'
*/
showForm.head = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\ItemController::edit
* @see app/Http/Controllers/ItemController.php:60
* @route '/items/{item}/edit'
*/
export const edit = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/items/{item}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ItemController::edit
* @see app/Http/Controllers/ItemController.php:60
* @route '/items/{item}/edit'
*/
edit.url = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { item: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            item: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return edit.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ItemController::edit
* @see app/Http/Controllers/ItemController.php:60
* @route '/items/{item}/edit'
*/
edit.get = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::edit
* @see app/Http/Controllers/ItemController.php:60
* @route '/items/{item}/edit'
*/
edit.head = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ItemController::edit
* @see app/Http/Controllers/ItemController.php:60
* @route '/items/{item}/edit'
*/
const editForm = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::edit
* @see app/Http/Controllers/ItemController.php:60
* @route '/items/{item}/edit'
*/
editForm.get = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ItemController::edit
* @see app/Http/Controllers/ItemController.php:60
* @route '/items/{item}/edit'
*/
editForm.head = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\ItemController::update
* @see app/Http/Controllers/ItemController.php:74
* @route '/items/{item}'
*/
export const update = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/items/{item}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\ItemController::update
* @see app/Http/Controllers/ItemController.php:74
* @route '/items/{item}'
*/
update.url = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { item: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            item: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return update.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ItemController::update
* @see app/Http/Controllers/ItemController.php:74
* @route '/items/{item}'
*/
update.put = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\ItemController::update
* @see app/Http/Controllers/ItemController.php:74
* @route '/items/{item}'
*/
update.patch = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\ItemController::update
* @see app/Http/Controllers/ItemController.php:74
* @route '/items/{item}'
*/
const updateForm = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ItemController::update
* @see app/Http/Controllers/ItemController.php:74
* @route '/items/{item}'
*/
updateForm.put = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ItemController::update
* @see app/Http/Controllers/ItemController.php:74
* @route '/items/{item}'
*/
updateForm.patch = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\ItemController::destroy
* @see app/Http/Controllers/ItemController.php:88
* @route '/items/{item}'
*/
export const destroy = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/items/{item}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\ItemController::destroy
* @see app/Http/Controllers/ItemController.php:88
* @route '/items/{item}'
*/
destroy.url = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { item: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            item: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return destroy.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ItemController::destroy
* @see app/Http/Controllers/ItemController.php:88
* @route '/items/{item}'
*/
destroy.delete = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\ItemController::destroy
* @see app/Http/Controllers/ItemController.php:88
* @route '/items/{item}'
*/
const destroyForm = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ItemController::destroy
* @see app/Http/Controllers/ItemController.php:88
* @route '/items/{item}'
*/
destroyForm.delete = (args: { item: string | number | { id: string | number } } | [item: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const ItemController = { index, create, store, show, edit, update, destroy }

export default ItemController