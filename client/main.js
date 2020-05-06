const playlist = makePlaylist()
let N = 0
const playBtn = document.getElementById("playBtn")
playBtn.onclick = Play
const infoEl = document.getElementById("info")
const infoMainEl = infoEl.querySelector("#info-main")
const infoDescEl = infoEl.querySelector("#info-desc")
resizeToPerfect(infoEl)
bringToMiddle(infoEl)
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
function randomSong () {
	return Math.floor(Math.random() * numberOfSongs)
}
function makePlaylist () {
	let list = []
	for(let i = 0; i < numberOfSongs; i++) {
		let next
		while((next = randomSong()) in list);
		list.push(next)
	}
	return list
}
function nextSong () {
	if(N >= numberOfSongs) N = 0
	return playlist[N++]
}
function Play () {
	audioEl.pause()
	audioEl.remove()
	const currentSong = nextSong()
	if(currentSong in sessionStorage)
		_Play(sessionStorage.getItem(currentSong))
	else
		getUrl(`${dbPath}/${currentSong}`, function (client) {
			const text = client.responseText
			_Play(text)
			sessionStorage.setItem(currentSong, text)
		})
	/* And download meta data of the next song as well */
	const next = nextSong(); N--
	if(! (next in sessionStorage))
		getUrl(`${dbPath}/${next}`, function (client) {
			sessionStorage.setItem(next, client.responseText)
		})
}
function _Play (text) {
	let meta = text.split('\n\n')
	meta[3] = meta[3].replace(/\n/g, "<br>")
	infoMainEl.innerHTML = `${meta[0]}<br>${meta[1]}<br>
<a target="_blank" href="${meta[2]}">داگرتن</a>`
	infoDescEl.innerHTML = meta[3]
	audioEl.src = meta[2]
	audioEl.play()
	resizeToPerfect(infoEl)
	infoEl.style.marginTop = "0"
}
function bringToMiddle (el) {
	let top = (window.innerHeight / 2) -
	    (el.offsetHeight / 2) - 50
	if(top < 0) top = 0
	el.style.marginTop = `${top}px`
}
function resizeToPerfect (el) {
	const fs = Math.sqrt(window.innerWidth) + 5
	el.style.fontSize = `${fs}px`
}
window.onresize = function () {
	resizeToPerfect(infoEl)
}
