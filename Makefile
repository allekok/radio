all: client/client.js
client/client.js: client/main.js client/server.js
	cat client/server.js > client/client.js
	cat client/main.js >> client/client.js
