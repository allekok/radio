const playlist = makePlaylist()
const playBtn = document.getElementById("playBtn")
const playSvg = playBtn.querySelector("svg")
const infoEl = document.getElementById("info")
const infoMainEl = infoEl.querySelector("#info-main")
const infoDescEl = infoEl.querySelector("#info-desc")
const audioEl = document.createElement("AUDIO")

let N = 0

resizeToPerfect(infoEl)
resizeToPerfect(playSvg, 'w')
bringToMiddle(playBtn)
playBtn.onclick = Play
audioEl.onended = Play

function getUrl(url, callback) {
	const client = new XMLHttpRequest()
	client.open("get", url)
	client.onload = () => callback(client)
	client.send()
}
function randomSong() {
	return Math.floor(Math.random() * numberOfSongs)
}
function makePlaylist() {
	let list = []
	for(let i = 0; i < numberOfSongs; i++) {
		let next
		while(list.indexOf(next = randomSong()) !== -1);
		list.push(next)
	}
	return list
}
function nextSong() {
	if(N >= numberOfSongs) N = 0
	return playlist[N++]
}
function Play() {
	audioEl.pause()
	audioEl.remove()
	infoMainEl.innerHTML = '....'
	infoDescEl.innerHTML = ''
	const currentSong = nextSong()
	if(currentSong in sessionStorage)
		_Play(sessionStorage.getItem(currentSong))
	else
		getUrl(`${dbPath}/${currentSong}`, function (client) {
			const text = client.responseText
			_Play(text)
			sessionStorage.setItem(currentSong, text)
		})
	/* And download meta data of next songs as well */
	for(const next of [nextSong(), nextSong()])
		if(! (next in sessionStorage))
			getUrl(`${dbPath}/${next}`, function (client) {
				sessionStorage.setItem(next,
						       client.responseText)
			})
	N -= 2
}
function _Play(text) {
	let meta = text.split('\n\n')
	meta[3] = meta[3].replace(/\n/g, "<br>")
	infoMainEl.innerHTML = `${meta[0]}<br>${meta[1]}<br>
<a target="_blank" href="${meta[2]}">داگرتن</a>`
	infoDescEl.innerHTML = meta[3]
	audioEl.src = meta[2]
	audioEl.play()
	resizeToPerfect(infoEl)
	playBtn.style.marginTop = '0'
}
function bringToMiddle(el) {
	let top = (window.innerHeight / 2) -
	    (el.offsetHeight / 2) - 50
	if(top < 0) top = 0
	el.style.marginTop = `${top}px`
}
function resizeToPerfect(el, prop="fs") {
	const s = Math.sqrt(window.innerWidth)
	if(prop == "fs")
		el.style.fontSize = `${s+5}px`
	else
		el.style.width = `${s+130}px`
}
window.onresize = () => {
	resizeToPerfect(infoEl)
	resizeToPerfect(playSvg, 'w')
	if(!infoMainEl.innerHTML)
		bringToMiddle(playBtn)
}
window.onkeyup = e => {
	if(e.keyCode == 13 && e.srcElement != playBtn)
		playBtn.click()
}
