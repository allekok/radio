
const playBtn = document.getElementById("playBtn")
bringToMiddle(playBtn)
playBtn.onclick = Play
const infoEl = document.getElementById("info")
const infoMainEl = document.createElement("DIV")
const infoDescEl = document.createElement("DIV")
infoMainEl.id = "info-main"
infoDescEl.id = "info-desc"
infoEl.appendChild(infoMainEl)
infoEl.appendChild(infoDescEl)
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
	const currentTime = Date.now() / 1000
	let diffTime = currentTime - epochTime
	/* Every 3mins, 4secs. */
	diffTime = diffTime - ((184 - 1) * (diffTime / 184))
	if(diffTime < 0) diffTime = 0
	diffTime = Math.floor(diffTime)
	return diffTime % numberOfSongs
}
function Play () {
	const currentSong = nextSong()
	getUrl(`${dbPath}/${currentSong}`, function (client) {
		let meta = client.responseText.split('\n\n')
		meta[3] = meta[3].replace(/\n/g, "<br>")
		infoMainEl.innerHTML = `${meta[0]}<br>${meta[1]}<br>
<a target="_blank" href="${meta[2]}">داگرتن</a>`
		infoDescEl.innerHTML = meta[3]
		audioEl.src = meta[2]
		audioEl.play()
		playBtn.style.display = "none"
		resizeToPerfect(infoEl)
		bringToMiddle(infoMainEl)
	})
}
function bringToMiddle (el) {
	let top = (window.innerHeight / 2) -
	      (el.offsetHeight / 2) - 20
	if(top < 0) top = 0
	el.style.marginTop = `${top}px`
}
function resizeToPerfect (el) {
	const fs = Math.sqrt(window.innerWidth) + 5
	el.style.fontSize = `${fs}px`
}
window.onresize = function () {
	bringToMiddle(playBtn)
	resizeToPerfect(infoEl)
	bringToMiddle(infoMainEl)
}
