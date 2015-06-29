'use strict'

notificationApp = angular.module('notificationApp', [
    require('angular-toastr')
    require('angular-moment')
    require('angular-scrollbar')
    require('angular-websocket')
])

notificationApp.constant 'Version', require('../package.json').version

notificationApp.config [ '$interpolateProvider', '$sceProvider', '$httpProvider', ($interpolateProvider, $sceProvider, $httpProvider) ->
    $sceProvider.enabled false
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
    $interpolateProvider.startSymbol('[[').endSymbol(']]')
    return
]

#notificationApp.run ['WebsocketService', (WebsocketService) ->
#    WebsocketService.connect()
#    return
#]

notificationApp.controller 'WatchmanController', require('./controller/WatchManController')
