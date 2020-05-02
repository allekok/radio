const cacheName = 'resources'
self.addEventListener('install', function (event) {
	event.waitUntil(
		caches.open(cacheName).then(function (cache) {
			return cache.addAll([
				'service-worker.js',
				'index.html',
				'client/client.js',
				'client/sima.css',
			])
		})
	)
})
self.addEventListener('fetch', function (event) {
	event.respondWith(
		caches.match(event.request).then(function (resp) {
			return resp || fetch(event.request).then(function (R) {
				return R
			})
		}).catch(function () {
			return caches.match('index.html')
		})
	)
})
