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
    subtitle: 'и Трявна · 3 дни',
  },
  {
    folder: 'bulgaria-sunny-beach-nessebar',
    c1: '#0369a1',
    c2: '#fbbf24',
    title: 'Слънчев Бряг',
    subtitle: 'и Несебър · 3 дни',
  },
  {
    folder: 'bulgaria-varna',
    c1: '#1e40af',
    c2: '#38bdf8',
    title: 'Варна',
    subtitle: 'на море · 2 дни',
  },
  {
    folder: 'turkey-edirne',
    c1: '#b91c1c',
    c2: '#f97316',
    title: 'Одрин',
    subtitle: 'на пазар · 2 дни',
  },
  {
    folder: 'turkey-istanbul',
    c1: '#4c1d95',
    c2: '#ec4899',
    title: 'Истанбул',
    subtitle: '4 дни',
  },
  {
    folder: 'greece-alexandroupolis',
    c1: '#0e7490',
    c2: '#fde68a',
    title: 'Александруполи',
    subtitle: 'на море · 5 дни',
  },
]

function svg(mainText, subText, c1, c2, flip) {
  const [x1, y1, x2, y2] = flip ? [1, 0, 0, 1] : [0, 0, 1, 1]
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" role="img" aria-label="${mainText}">
  <defs>
    <linearGradient id="bg" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}">
      <stop offset="0%" stop-color="${c1}"/>
      <stop offset="100%" stop-color="${c2}"/>
    </linearGradient>
  </defs>
  <rect width="800" height="500" fill="url(#bg)"/>
  <text x="400" y="235" text-anchor="middle" fill="#ffffff" font-family="system-ui,sans-serif" font-size="36" font-weight="700">${mainText}</text>
  <text x="400" y="280" text-anchor="middle" fill="#ffffff" opacity="0.9" font-family="system-ui,sans-serif" font-size="20">${subText}</text>
</svg>`
}

for (const trip of trips) {
  const dir = path.join(base, trip.folder)
  fs.mkdirSync(dir, { recursive: true })

  fs.writeFileSync(
    path.join(dir, 'hero-1.svg'),
    svg(trip.title, trip.subtitle, trip.c1, trip.c2, false),
  )
  fs.writeFileSync(
    path.join(dir, 'hero-2.svg'),
    svg(trip.title, trip.subtitle, trip.c2, trip.c1, true),
  )
  fs.writeFileSync(
    path.join(dir, 'gallery-1.svg'),
    svg(trip.title, `${trip.subtitle} · галерия 1`, trip.c1, trip.c2, true),
  )
  fs.writeFileSync(
    path.join(dir, 'gallery-2.svg'),
    svg(trip.title, `${trip.subtitle} · галерия 2`, trip.c2, trip.c1, false),
  )
  fs.writeFileSync(
    path.join(dir, 'gallery-3.svg'),
    svg(trip.title, `${trip.subtitle} · галерия 3`, trip.c1, trip.c2, false),
  )
}

console.log(`Generated images for ${trips.length} trips`)
