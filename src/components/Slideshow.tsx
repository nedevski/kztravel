import { useEffect, useState } from 'react'
import { imageAltFromPath } from '@/lib/formatters'

interface SlideshowProps {
  images: string[]
  alt: string
  className?: string
  intervalMs?: number
}

export function Slideshow({
  images,
  alt,
  className = '',
  intervalMs = 4500,
}: SlideshowProps) {
  const [index, setIndex] = useState(0)

  useEffect(() => {
    if (images.length <= 1) return
    const timer = setInterval(() => {
      setIndex((i) => (i + 1) % images.length)
    }, intervalMs)
    return () => clearInterval(timer)
  }, [images.length, intervalMs])

  if (images.length === 0) return null

  return (
    <div className={`slideshow ${className}`}>
      {images.map((src, i) => (
        <img
          key={src}
          src={src}
          alt={imageAltFromPath(src, alt)}
          className={i === index ? 'slideshow__img active' : 'slideshow__img'}
          loading={i === 0 ? 'eager' : 'lazy'}
        />
      ))}
      {images.length > 1 && (
        <div className="slideshow__dots" aria-hidden="true">
          {images.map((_, i) => (
            <span
              key={i}
              className={i === index ? 'slideshow__dot active' : 'slideshow__dot'}
            />
          ))}
        </div>
      )}
    </div>
  )
}
