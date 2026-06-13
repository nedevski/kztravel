import { useEffect, useRef, useState, type MouseEvent, type PointerEvent } from 'react'
import { imageAltFromPath } from '@/lib/formatters'

interface SlideshowProps {
  images: string[]
  alt: string
  className?: string
  intervalMs?: number
}

const SWIPE_THRESHOLD_PX = 40

export function Slideshow({
  images,
  alt,
  className = '',
  intervalMs = 4500,
}: SlideshowProps) {
  const [index, setIndex] = useState(0)
  const [isDragging, setIsDragging] = useState(false)
  const dragStartX = useRef<number | null>(null)
  const dragPointerId = useRef<number | null>(null)
  const didSwipe = useRef(false)

  useEffect(() => {
    setIndex(0)
  }, [images])

  useEffect(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (images.length <= 1 || prefersReducedMotion || isDragging) return
    const timer = setInterval(() => {
      setIndex((i) => (i + 1) % images.length)
    }, intervalMs)
    return () => clearInterval(timer)
  }, [images.length, intervalMs, isDragging])

  const goTo = (direction: -1 | 1) => {
    setIndex((i) => (i + direction + images.length) % images.length)
  }

  const handlePointerDown = (event: PointerEvent<HTMLDivElement>) => {
    if (images.length <= 1) return
    dragStartX.current = event.clientX
    dragPointerId.current = event.pointerId
    setIsDragging(true)
    event.currentTarget.setPointerCapture(event.pointerId)
  }

  const handlePointerUp = (event: PointerEvent<HTMLDivElement>) => {
    if (dragStartX.current === null || dragPointerId.current !== event.pointerId) return

    const deltaX = event.clientX - dragStartX.current
    if (Math.abs(deltaX) >= SWIPE_THRESHOLD_PX) {
      didSwipe.current = true
      goTo(deltaX < 0 ? 1 : -1)
    }

    dragStartX.current = null
    dragPointerId.current = null
    setIsDragging(false)

    if (event.currentTarget.hasPointerCapture(event.pointerId)) {
      event.currentTarget.releasePointerCapture(event.pointerId)
    }
  }

  const handlePointerCancel = (event: PointerEvent<HTMLDivElement>) => {
    dragStartX.current = null
    dragPointerId.current = null
    setIsDragging(false)

    if (event.currentTarget.hasPointerCapture(event.pointerId)) {
      event.currentTarget.releasePointerCapture(event.pointerId)
    }
  }

  const handleClick = (event: MouseEvent<HTMLDivElement>) => {
    if (!didSwipe.current) return
    event.preventDefault()
    event.stopPropagation()
    didSwipe.current = false
  }

  if (images.length === 0) return null

  const safeIndex = index % images.length

  return (
    <div
      className={`slideshow ${className}`}
      onPointerDown={handlePointerDown}
      onPointerUp={handlePointerUp}
      onPointerCancel={handlePointerCancel}
      onClick={handleClick}
    >
      {images.map((src, i) => (
        <img
          key={src}
          src={src}
          alt={imageAltFromPath(src, alt)}
          className={i === safeIndex ? 'slideshow__img active' : 'slideshow__img'}
          loading={i === 0 ? 'eager' : 'lazy'}
          draggable={false}
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
