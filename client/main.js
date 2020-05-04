const playBtn = document.getElementById("playBtn")
playBtn.onclick = Play
bringToMiddle(playBtn)
const infoEl = document.getElementById("info")
const infoMainEl = infoEl.querySelector("#info-main")
const infoDescEl = infoEl.querySelector("#info-desc")
resizeToPerfect(infoEl)
const audioEl = document.createElement("AUDIO")
audioEl.onended = Play
function getUrl(url, callback) {
	const client = new XMLHttpRequest()
	client.open("get", url)
	client.onload = function () {
		callback(this)
	}
	client.send()
}
function nextSong () {
	return Math.floor(Math.random() * numberOfSongs)
}
function Play () {
	const currentSong = nextSong()
	getUrl(`${dbPath}/${currentSong}`, function (client) {
		let meta = client.responseText.split('\n\n')
		meta[3] = meta[3].replace(/\n/g, "<br>")
		infoMainEl.innerHTML = `${meta[0]}<br>${meta[1]}<br>
<a target="_blank" href="${meta[2]}">داگرتن</a>`
		infoDescEl.innerHTML = meta[3]
		audioEl.remove()
		audioEl.src = meta[2]
		audioEl.play()
		resizeToPerfect(infoEl)
		bringToMiddle(playBtn)
	})
}
function bringToMiddle (el) {
	let top = (window.innerHeight / 2) -
	    (el.offsetHeight / 2) - 150
	if(top < 0) top = 0
	el.style.marginTop = `${top}px`
}
function resizeToPerfect (el) {
	const fs = Math.sqrt(window.innerWidth) + 5
	el.style.fontSize = `${fs}px`
}
window.onresize = function () {
	resizeToPerfect(infoEl)
	bringToMiddle(playBtn)
}
