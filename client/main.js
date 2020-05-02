let currentSong = -1
const playBtn = document.getElementById("playBtn")
bringToMiddle(playBtn)
playBtn.onclick = Play
const infoEl = document.getElementById("info")
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
	/* Every 84 Seconds */
	diffTime = diffTime - ((84 - 1) * (diffTime / 84))
	if(diffTime < 0) diffTime = 0
	diffTime = Math.floor(diffTime)
	return diffTime % numberOfSongs
}
function Play () {
	if(currentSong >= numberOfSongs) currentSong = 0
	else currentSong = nextSong()
	getUrl(`${dbPath}/${currentSong}`, function (client) {
		let meta = client.responseText.split('\n')
		infoEl.innerHTML = `${meta[1]}<br>${meta[2]}<br>
<a target="_blank" href="${meta[3]}">داگرتن</a>`
		audioEl.src = meta[3]
		audioEl.play()
		playBtn.style.display = "none"
		resizeToPerfect(infoEl)
		bringToMiddle(infoEl)
	})
}
function bringToMiddle (el) {
	const top = (window.innerHeight / 2) -
	      (el.offsetHeight / 2) - 20
	el.style.marginTop = `${top}px`
}
function resizeToPerfect (el) {
	const fs = Math.sqrt(window.innerWidth) + 5
	el.style.fontSize = `${fs}px`
}
window.onresize = function () {
	bringToMiddle(playBtn)
	resizeToPerfect(infoEl)
	bringToMiddle(infoEl)
}
