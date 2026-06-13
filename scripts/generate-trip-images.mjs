import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const base = path.join(__dirname, '../public/images/trips')

const trips = [
  {
    folder: 'bulgaria-veliko-tarnovo',
    c1: '#7c2d12',
    c2: '#f59e0b',
    title: 'Велико Търново',
    heroScenes: ['tsarevets', 'tryavna'],
    gallery: [
      { title: 'Царевец', scene: 'tsarevets' },
      { title: 'Трявна', scene: 'tryavna' },
      { title: 'Арбанаси', scene: 'arbanasi' },
    ],
  },
  {
    folder: 'bulgaria-sunny-beach-nessebar',
    c1: '#0369a1',
    c2: '#fbbf24',
    title: 'Слънчев Бряг',
    heroScenes: ['beach', 'nessebar'],
    gallery: [
      { title: 'Слънчев бряг', scene: 'beach' },
      { title: 'Старият Несебър', scene: 'nessebar' },
      { title: 'Крайбрежна алея', scene: 'promenade' },
    ],
  },
  {
    folder: 'bulgaria-varna',
    c1: '#1e40af',
    c2: '#38bdf8',
    title: 'Варна',
    heroScenes: ['cathedral', 'seaGarden'],
    gallery: [
      { title: 'Морска градина', scene: 'seaGarden' },
      { title: 'Катедрала „Св. Успение"', scene: 'cathedral' },
      { title: 'Археологически музей', scene: 'museum' },
    ],
  },
  {
    folder: 'turkey-edirne',
    c1: '#b91c1c',
    c2: '#f97316',
    title: 'Одрин',
    heroScenes: ['selimiye', 'kapiKule'],
    gallery: [
      { title: 'Селимие джамия', scene: 'selimiye' },
      { title: 'Капи куле', scene: 'kapiKule' },
      { title: 'Пазар с подправки', scene: 'spiceMarket' },
    ],
  },
  {
    folder: 'turkey-istanbul',
    c1: '#4c1d95',
    c2: '#ec4899',
    title: 'Истанбул',
    heroScenes: ['hagiaSophia', 'galataTower'],
    gallery: [
      { title: 'Айя София', scene: 'hagiaSophia' },
      { title: 'Галатска кула', scene: 'galataTower' },
      { title: 'Босфор', scene: 'bosphorus' },
    ],
  },
  {
    folder: 'greece-alexandroupolis',
    c1: '#0e7490',
    c2: '#fde68a',
    title: 'Александруполи',
    heroScenes: ['lighthouse', 'makriBeach'],
    gallery: [
      { title: 'Фарът на Александруполи', scene: 'lighthouse' },
      { title: 'Маронея', scene: 'maroneia' },
      { title: 'Макри', scene: 'makriBeach' },
    ],
  },
]

function defs(c1, c2, flip) {
  const [x1, y1, x2, y2] = flip ? [1, 0, 0, 1] : [0, 0, 1, 1]
  return `<defs>
    <linearGradient id="sky" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}">
      <stop offset="0%" stop-color="${c1}"/>
      <stop offset="55%" stop-color="${c2}"/>
      <stop offset="100%" stop-color="${c2}" stop-opacity="0.85"/>
    </linearGradient>
    <linearGradient id="haze" x1="0" y1="0.35" x2="0" y2="1">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0"/>
      <stop offset="45%" stop-color="#ffffff" stop-opacity="0.06"/>
      <stop offset="100%" stop-color="#0f172a" stop-opacity="0.22"/>
    </linearGradient>
    <linearGradient id="sea" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#0c4a6e" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="#082f49" stop-opacity="0.65"/>
    </linearGradient>
    <linearGradient id="sand" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#fef3c7" stop-opacity="0.25"/>
      <stop offset="100%" stop-color="#d97706" stop-opacity="0.15"/>
    </linearGradient>
    <linearGradient id="scrim" x1="0" y1="0.55" x2="0" y2="1">
      <stop offset="0%" stop-color="#000000" stop-opacity="0"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0.45"/>
    </linearGradient>
    <radialGradient id="sun" cx="0.78" cy="0.18" r="0.28">
      <stop offset="0%" stop-color="#fff7ed" stop-opacity="0.55"/>
      <stop offset="55%" stop-color="#fde68a" stop-opacity="0.18"/>
      <stop offset="100%" stop-color="#fde68a" stop-opacity="0"/>
    </radialGradient>
  </defs>`
}

function backdrop() {
  return `<rect width="800" height="500" fill="url(#sky)"/>
  <rect width="800" height="500" fill="url(#sun)"/>
  <rect width="800" height="500" fill="url(#haze)"/>`
}

function ridge(d, opacity = 0.18) {
  return `<path d="${d}" fill="#0f172a" opacity="${opacity}"/>`
}

function silhouette(d, opacity = 0.42) {
  return `<path d="${d}" fill="#0f172a" opacity="${opacity}"/>`
}

function water(y = 340, depth = 160) {
  return `<rect x="0" y="${y}" width="800" height="${depth}" fill="url(#sea)"/>
  <path d="M0 ${y + 8} C120 ${y - 4} 240 ${y + 10} 400 ${y + 4} S680 ${y - 6} 800 ${y + 8}" stroke="#e0f2fe" stroke-width="1.2" fill="none" opacity="0.22"/>
  <path d="M0 ${y + 28} C160 ${y + 16} 320 ${y + 34} 480 ${y + 22} S720 ${y + 30} 800 ${y + 24}" stroke="#e0f2fe" stroke-width="0.8" fill="none" opacity="0.14"/>
  <path d="M0 ${y + 52} C200 ${y + 42} 400 ${y + 58} 600 ${y + 46} S760 ${y + 54} 800 ${y + 50}" stroke="#bae6fd" stroke-width="0.6" fill="none" opacity="0.1"/>`
}

function shore(y = 370) {
  return `<path d="M0 ${y} C140 ${y - 18} 280 ${y + 6} 420 ${y - 10} S700 ${y + 4} 800 ${y - 6} L800 500 L0 500 Z" fill="url(#sand)"/>`
}

function clouds() {
  return `<ellipse cx="140" cy="95" rx="95" ry="22" fill="#ffffff" opacity="0.1"/>
  <ellipse cx="210" cy="88" rx="60" ry="16" fill="#ffffff" opacity="0.08"/>
  <ellipse cx="520" cy="72" rx="110" ry="20" fill="#ffffff" opacity="0.09"/>
  <ellipse cx="640" cy="80" rx="70" ry="15" fill="#ffffff" opacity="0.07"/>`
}

function trees(y, spans) {
  return spans
    .map(([x, h]) => {
      const w = h * 0.9
      return `<ellipse cx="${x}" cy="${y - h * 0.45}" rx="${w}" ry="${h * 0.55}" fill="#0f172a" opacity="0.2"/>
  <rect x="${x - 3}" y="${y - h * 0.2}" width="6" height="${h * 0.35}" fill="#0f172a" opacity="0.18"/>`
    })
    .join('\n  ')
}

function scene(sceneName) {
  switch (sceneName) {
    case 'tsarevets':
      return `${clouds()}
  ${ridge('M0 310 C120 250 220 235 340 255 S520 220 640 245 S760 270 800 290 L800 500 L0 500 Z', 0.12)}
  ${ridge('M0 350 C160 300 300 285 420 305 S620 290 800 330 L800 500 L0 500 Z', 0.16)}
  ${water(365, 135)}
  ${silhouette('M0 365 C90 350 170 330 250 318 C300 268 350 235 395 218 C420 198 445 205 468 225 C505 210 540 232 565 248 C590 236 615 250 635 268 C670 255 710 275 800 300 L800 365 L0 365 Z', 0.5)}
  ${silhouette('M395 218 C405 188 418 168 430 155 C438 175 445 195 452 210 C442 205 430 205 418 212 Z', 0.55)}`

    case 'tryavna':
      return `${clouds()}
  ${ridge('M0 330 C150 280 280 265 400 285 S620 260 800 300 L800 500 L0 500 Z', 0.14)}
  ${ridge('M0 360 C180 320 340 310 500 328 S700 315 800 345 L800 500 L0 500 Z', 0.18)}
  ${silhouette('M60 360 C120 340 180 330 230 338 C250 300 265 275 278 248 C286 275 295 305 305 335 C360 325 420 318 480 328 C510 305 530 285 545 262 C558 290 570 318 585 340 C640 332 700 340 760 355 L760 360 L60 360 Z', 0.46)}
  ${silhouette('M278 248 L272 220 L285 200 L298 220 L292 248 Z', 0.52)}
  <path d="M120 360 C220 352 320 348 420 355 S620 350 720 358" stroke="#0f172a" stroke-width="1" opacity="0.12" fill="none"/>`

    case 'arbanasi':
      return `${clouds()}
  ${ridge('M0 300 C200 250 380 240 560 265 S740 255 800 275 L800 500 L0 500 Z', 0.13)}
  ${ridge('M0 340 C160 305 320 295 480 315 S680 300 800 335 L800 500 L0 500 Z', 0.17)}
  ${silhouette('M0 340 C200 320 340 305 420 295 C455 268 470 248 480 230 C490 248 505 270 525 292 C590 285 660 300 800 325 L800 340 Z', 0.44)}
  ${silhouette('M468 230 C478 205 488 188 498 175 C508 192 515 210 520 228 C505 222 488 222 472 228 Z', 0.5)}
  ${trees(340, [[150, 42], [220, 55], [680, 48], [740, 38]])}`

    case 'beach':
      return `${clouds()}
  ${water(300, 200)}
  ${shore(355)}
  ${ridge('M0 305 C120 285 240 278 360 290 S580 275 800 295 L800 355 L0 355 Z', 0.1)}
  <path d="M0 355 C200 340 400 348 600 338 S760 345 800 340" stroke="#ffffff" stroke-width="0.8" opacity="0.15" fill="none"/>`

    case 'nessebar':
      return `${clouds()}
  ${water(315, 185)}
  ${silhouette('M180 315 C220 300 260 292 300 295 C320 275 340 258 360 245 C375 262 388 280 400 295 C430 288 460 282 490 288 C510 270 525 255 540 242 C555 258 570 275 585 292 C620 285 660 290 700 305 L700 315 Z', 0.48)}
  ${silhouette('M350 245 C358 222 368 205 378 192 C388 208 395 225 400 242 C390 238 378 238 368 242 Z', 0.52)}
  ${silhouette('M530 242 C538 218 548 200 558 186 C568 202 575 220 580 238 C570 234 558 234 548 238 Z', 0.52)}
  ${shore(355)}`

    case 'promenade':
      return `${clouds()}
  ${water(305, 195)}
  <path d="M0 338 C180 325 360 332 540 322 S760 330 800 325" stroke="#f8fafc" stroke-width="2.5" opacity="0.2" fill="none"/>
  <path d="M0 348 C200 338 400 345 600 336 S760 342 800 338" stroke="#f8fafc" stroke-width="1" opacity="0.1" fill="none"/>
  ${trees(335, [[90, 35], [180, 42], [620, 38], [710, 32]])}
  ${shore(352)}`

    case 'cathedral':
      return `${clouds()}
  ${water(325, 175)}
  ${ridge('M0 310 C180 285 360 278 540 295 S740 285 800 305 L800 325 L0 325 Z', 0.12)}
  ${silhouette('M0 325 C120 315 220 305 310 300 C340 268 360 242 378 220 C395 245 410 272 425 298 C480 292 540 288 600 295 C630 270 650 250 665 232 C680 252 695 275 710 298 C740 295 770 305 800 312 L800 325 Z', 0.46)}
  ${silhouette('M378 220 C388 188 400 165 412 145 C424 168 432 192 438 215 C420 208 400 208 385 215 Z', 0.52)}`

    case 'seaGarden':
      return `${clouds()}
  ${water(330, 170)}
  ${ridge('M0 320 C200 295 400 288 600 305 S760 298 800 315 L800 330 L0 330 Z', 0.11)}
  ${trees(330, [[80, 52], [150, 65], [230, 58], [310, 72], [400, 60], [490, 68], [580, 55], [660, 62], [740, 50]])}
  <path d="M60 330 C200 318 340 322 480 315 S700 320 800 312" stroke="#0f172a" stroke-width="1.5" opacity="0.12" fill="none"/>
  ${shore(348)}`

    case 'museum':
      return `${clouds()}
  ${ridge('M0 305 C200 275 400 268 600 288 S760 280 800 300 L800 500 L0 500 Z', 0.13)}
  ${trees(340, [[100, 45], [170, 52], [630, 48], [700, 40]])}
  ${silhouette('M220 340 L260 340 L270 290 L290 250 L310 230 L330 250 L350 290 L360 340 L440 340 L450 295 L470 255 L490 235 L510 255 L530 295 L540 340 L580 340 L580 355 L220 355 Z', 0.4)}
  <path d="M180 355 C320 348 480 352 620 345" stroke="#0f172a" stroke-width="1" opacity="0.1" fill="none"/>`

    case 'selimiye':
      return `${clouds()}
  ${ridge('M0 315 C180 280 360 270 540 290 S740 278 800 305 L800 500 L0 500 Z', 0.14)}
  ${silhouette('M0 315 C150 300 280 292 360 288 C390 255 410 228 430 205 C450 228 470 255 490 285 C540 278 590 282 640 292 C670 268 690 248 705 228 C722 250 738 275 755 298 C770 295 785 302 800 308 L800 315 Z', 0.48)}
  ${silhouette('M430 205 C448 168 465 142 480 118 C495 145 508 172 518 200 C500 192 478 192 460 200 Z', 0.54)}
  ${silhouette('M360 288 L352 248 L358 218 L366 248 Z M640 292 L632 250 L640 218 L648 250 Z', 0.5)}`

    case 'kapiKule':
      return `${clouds()}
  ${water(355, 145)}
  ${ridge('M0 330 C200 305 400 298 600 318 S760 310 800 330 L800 355 L0 355 Z', 0.15)}
  ${silhouette('M310 355 L310 210 L340 175 L370 155 L400 140 L430 155 L460 175 L490 210 L490 355 Z', 0.45)}
  ${silhouette('M370 155 L400 115 L430 155 Z', 0.5)}
  <path d="M120 355 C280 345 520 348 680 352" stroke="#0f172a" stroke-width="2" opacity="0.15" fill="none"/>
  <path d="M120 365 C400 352 600 358 680 360" stroke="#0f172a" stroke-width="1" opacity="0.08" fill="none"/>`

    case 'spiceMarket':
      return `${clouds()}
  ${ridge('M0 300 C200 275 400 268 600 285 S760 278 800 298 L800 500 L0 500 Z', 0.12)}
  ${silhouette('M100 340 C140 320 180 312 220 315 C260 305 300 300 340 305 C380 298 420 295 460 300 C500 295 540 292 580 298 C620 292 660 295 700 305 L700 340 L100 340 Z', 0.38)}
  ${silhouette('M120 315 C180 300 240 292 300 295 C360 288 420 285 480 290 C540 285 600 288 660 298 C620 278 560 268 500 265 C440 260 380 262 320 268 C260 272 200 282 140 295 Z', 0.32)}
  <rect x="0" y="300" width="800" height="200" fill="#ffffff" opacity="0.04"/>`

    case 'hagiaSophia':
      return `${clouds()}
  ${water(330, 170)}
  ${ridge('M0 305 C160 285 320 278 480 292 S680 282 800 300 L800 330 L0 330 Z', 0.13)}
  ${silhouette('M0 330 C100 322 180 315 250 310 C280 278 300 252 320 228 C340 252 358 280 375 308 C420 302 465 298 510 305 C535 278 555 255 572 232 C590 258 608 285 625 310 C680 305 740 312 800 320 L800 330 Z', 0.47)}
  ${silhouette('M320 228 C332 195 345 168 358 145 C372 172 382 200 390 225 C375 218 358 218 342 225 Z', 0.53)}
  ${silhouette('M250 310 L242 265 L248 230 L256 265 Z M550 305 L542 260 L550 225 L558 260 Z', 0.48)}`

    case 'galataTower':
      return `${clouds()}
  ${water(325, 175)}
  ${ridge('M0 310 C200 290 400 282 600 300 S760 292 800 312 L800 330 L0 330 Z', 0.14)}
  ${silhouette('M0 330 C120 322 220 315 300 312 C310 280 320 240 328 195 C336 155 345 125 355 100 C365 125 374 155 382 195 C390 240 398 280 408 312 C480 308 560 312 640 320 C660 295 675 275 688 255 C702 278 715 302 728 322 C755 318 780 325 800 330 L800 330 Z', 0.44)}
  ${silhouette('M355 100 L340 135 L370 135 Z M382 195 L400 55 L418 195 Z', 0.5)}`

    case 'bosphorus':
      return `${clouds()}
  ${water(285, 215)}
  ${silhouette('M0 285 C120 272 220 268 300 275 C340 258 380 248 420 242 C460 250 500 262 540 272 C600 265 660 270 800 282 L800 285 Z', 0.35)}
  ${silhouette('M0 295 C100 288 200 285 280 290 C320 278 360 270 400 265 C440 272 480 282 520 288 C580 282 660 288 800 295 L800 298 L0 298 Z', 0.28)}
  <path d="M80 285 L720 285" stroke="#0f172a" stroke-width="3" opacity="0.2"/>
  <path d="M160 285 L160 298 M320 285 L320 298 M480 285 L480 298 M640 285 L640 298" stroke="#0f172a" stroke-width="2" opacity="0.15"/>
  <ellipse cx="400" cy="120" rx="180" ry="40" fill="#ffffff" opacity="0.05"/>`

    case 'lighthouse':
      return `${clouds()}
  ${water(310, 190)}
  ${shore(348)}
  ${ridge('M0 320 C200 300 400 295 600 310 S760 305 800 318 L800 348 L0 348 Z', 0.12)}
  ${silhouette('M0 348 C180 335 340 330 500 338 C520 310 540 275 555 235 C570 275 585 310 605 338 C680 332 750 340 800 348 L800 348 Z', 0.4)}
  ${silhouette('M555 235 L548 195 L555 155 L562 195 Z M555 155 L540 185 L570 185 Z', 0.48)}
  <path d="M555 195 L780 250" stroke="#fde68a" stroke-width="1.5" opacity="0.12" fill="none"/>
  <path d="M555 195 L720 220" stroke="#fde68a" stroke-width="0.8" opacity="0.08" fill="none"/>`

    case 'maroneia':
      return `${clouds()}
  ${water(340, 160)}
  ${ridge('M0 310 C180 275 360 265 540 285 S740 272 800 300 L800 340 L0 340 Z', 0.14)}
  ${ridge('M0 340 C200 315 400 308 600 325 S760 318 800 338 L800 500 L0 500 Z', 0.18)}
  ${silhouette('M0 340 C150 328 280 320 380 325 C400 305 420 290 440 278 C455 295 468 312 480 328 C540 322 600 328 660 338 C700 325 750 335 800 345 L800 340 Z', 0.38)}
  ${silhouette('M420 278 C435 258 448 242 460 228 C472 245 480 262 488 278 C475 272 462 272 450 276 Z', 0.42)}
  <ellipse cx="300" cy="328" rx="35" ry="8" fill="#0f172a" opacity="0.15"/>
  <ellipse cx="520" cy="332" rx="45" ry="10" fill="#0f172a" opacity="0.12"/>`

    case 'makriBeach':
      return `${clouds()}
  ${water(320, 180)}
  ${shore(358)}
  ${ridge('M0 305 C160 280 320 272 480 288 S680 278 800 300 L800 358 L0 358 Z', 0.13)}
  ${trees(305, [[120, 38], [180, 48], [240, 42], [580, 45], [640, 52], [700, 40]])}
  <path d="M0 358 C250 345 500 352 750 342" stroke="#ffffff" stroke-width="0.6" opacity="0.12" fill="none"/>`

    default:
      return ''
  }
}

function wrap(title, sceneName, c1, c2, flip, fontSize, labelY) {
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" role="img" aria-label="${title}">
  ${defs(c1, c2, flip)}
  ${backdrop()}
  ${scene(sceneName)}
  <rect width="800" height="500" fill="url(#scrim)"/>
  <text x="400" y="${labelY}" text-anchor="middle" fill="#ffffff" font-family="system-ui,sans-serif" font-size="${fontSize}" font-weight="600" letter-spacing="0.02em">${title}</text>
</svg>`
}

function heroSvg(title, sceneName, c1, c2, flip) {
  return wrap(title, sceneName, c1, c2, flip, 34, 462)
}

function gallerySvg(title, sceneName, c1, c2, flip) {
  return wrap(title, sceneName, c1, c2, flip, 26, 468)
}

for (const trip of trips) {
  const dir = path.join(base, trip.folder)
  fs.mkdirSync(dir, { recursive: true })

  fs.writeFileSync(
    path.join(dir, 'hero-1.svg'),
    heroSvg(trip.title, trip.heroScenes[0], trip.c1, trip.c2, false),
  )
  fs.writeFileSync(
    path.join(dir, 'hero-2.svg'),
    heroSvg(trip.title, trip.heroScenes[1], trip.c1, trip.c2, true),
  )

  trip.gallery.forEach((item, index) => {
    fs.writeFileSync(
      path.join(dir, `gallery-${index + 1}.svg`),
      gallerySvg(item.title, item.scene, trip.c1, trip.c2, index % 2 === 1),
    )
  })
}

console.log(`Generated images for ${trips.length} trips`)
