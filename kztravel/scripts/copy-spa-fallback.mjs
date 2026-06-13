import { copyFileSync } from 'node:fs'
import { resolve } from 'node:path'

const index = resolve('dist/index.html')
copyFileSync(index, resolve('dist/404.html'))
