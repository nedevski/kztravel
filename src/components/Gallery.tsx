import { useCallback, useEffect, useState } from 'react'
import { imageAltFromPath } from '@/lib/formatters'
import { ui } from '@/lib/strings'

interface GalleryProps {
  images: string[]
  tripName: string
}

export function Gallery({ images, tripName }: GalleryProps) {
  const [activeIndex, setActiveIndex] = useState<number | null>(null)

  const close = useCallback(() => setActiveIndex(null), [])

  const goPrev = useCallback(() => {
    setActiveIndex((i) => (i === null ? null : (i - 1 + images.length) % images.length))
  }, [images.length])

  const goNext = useCallback(() => {
    setActiveIndex((i) => (i === null ? null : (i + 1) % images.length))
  }, [images.length])

  useEffect(() => {
    if (activeIndex === null) return

    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'ArrowLeft') {
        e.preventDefault()
        goPrev()
      } else if (e.key === 'ArrowRight') {
        e.preventDefault()
        goNext()
      } else if (e.key === 'Escape') {
        e.preventDefault()
        close()
      }
    }

    document.addEventListener('keydown', onKeyDown)
    document.body.style.overflow = 'hidden'

    return () => {
      document.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
    }
  }, [activeIndex, close, goPrev, goNext])

  if (images.length === 0) return null

  const activeSrc = activeIndex !== null ? images[activeIndex] : null

  return (
    <>
      <div className="gallery">
        {images.map((src, i) => (
          <button
            key={src}
            type="button"
            className="gallery__thumb"
            onClick={() => setActiveIndex(i)}
            aria-label={ui.viewPhoto(i + 1, images.length)}
          >
            <img
              src={src}
              alt={imageAltFromPath(src, tripName)}
              className="gallery__img"
              loading="lazy"
            />
          </button>
        ))}
      </div>

      {activeSrc !== null && activeIndex !== null && (
        <div
          className="gallery-lightbox"
          role="dialog"
          aria-modal="true"
          aria-label={ui.photoGallery(tripName)}
          onClick={close}
        >
          <div className="gallery-lightbox__content" onClick={(e) => e.stopPropagation()}>
            <button
              type="button"
              className="gallery-lightbox__close"
              onClick={close}
              aria-label={ui.closeGallery}
            >
              &times;
            </button>

            {images.length > 1 && (
              <button
                type="button"
                className="gallery-lightbox__nav gallery-lightbox__nav--prev"
                onClick={goPrev}
                aria-label={ui.previousPhoto}
              >
                &#8249;
              </button>
            )}

            <img
              src={activeSrc}
              alt={imageAltFromPath(activeSrc, tripName)}
              className="gallery-lightbox__img"
            />

            {images.length > 1 && (
              <button
                type="button"
                className="gallery-lightbox__nav gallery-lightbox__nav--next"
                onClick={goNext}
                aria-label={ui.nextPhoto}
              >
                &#8250;
              </button>
            )}

            {images.length > 1 && (
              <p className="gallery-lightbox__counter" aria-live="polite">
                {activeIndex + 1} / {images.length}
              </p>
            )}
          </div>
        </div>
      )}
    </>
  )
}
