import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AIController::index
* @see app/Http/Controllers/AIController.php:19
* @route '/ai'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/ai',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AIController::index
* @see app/Http/Controllers/AIController.php:19
* @route '/ai'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AIController::index
* @see app/Http/Controllers/AIController.php:19
* @route '/ai'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AIController::index
* @see app/Http/Controllers/AIController.php:19
* @route '/ai'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AIController::index
* @see app/Http/Controllers/AIController.php:19
* @route '/ai'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AIController::index
* @see app/Http/Controllers/AIController.php:19
* @route '/ai'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AIController::index
* @see app/Http/Controllers/AIController.php:19
* @route '/ai'
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
* @see \App\Http\Controllers\AIController::chat
* @see app/Http/Controllers/AIController.php:24
* @route '/ai/chat'
*/
export const chat = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chat.url(options),
    method: 'post',
})

chat.definition = {
    methods: ["post"],
    url: '/ai/chat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AIController::chat
* @see app/Http/Controllers/AIController.php:24
* @route '/ai/chat'
*/
chat.url = (options?: RouteQueryOptions) => {
    return chat.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AIController::chat
* @see app/Http/Controllers/AIController.php:24
* @route '/ai/chat'
*/
chat.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chat.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AIController::chat
* @see app/Http/Controllers/AIController.php:24
* @route '/ai/chat'
*/
const chatForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: chat.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AIController::chat
* @see app/Http/Controllers/AIController.php:24
* @route '/ai/chat'
*/
chatForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: chat.url(options),
    method: 'post',
})

chat.form = chatForm

const AIController = { index, chat }

export default AIController