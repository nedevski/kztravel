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
    setIndex(0)
  }, [images])

  useEffect(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (images.length <= 1 || prefersReducedMotion) return
    const timer = setInterval(() => {
      setIndex((i) => (i + 1) % images.length)
    }, intervalMs)
    return () => clearInterval(timer)
  }, [images.length, intervalMs])

  if (images.length === 0) return null

  const safeIndex = index % images.length

  return (
    <div className={`slideshow ${className}`}>
      {images.map((src, i) => (
        <img
          key={src}
          src={src}
          alt={imageAltFromPath(src, alt)}
          className={i === safeIndex ? 'slideshow__img active' : 'slideshow__img'}
          loading={i === 0 ? 'eager' : 'lazy'}
        />
      ))}
      {images.length > 1 && (
        <div className="slideshow__dots" aria-hidden="true">
          {images.map((_, i) => (
            <span
              key={i}
              className={i === safeIndex ? 'slideshow__dot active' : 'slideshow__dot'}
            />
          ))}
        </div>
      )}
    </div>
  )
}
