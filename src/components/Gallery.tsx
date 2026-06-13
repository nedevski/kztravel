import { useCallback, useEffect, useRef, useState } from 'react'
import { imageAltFromPath } from '@/lib/formatters'
import { ui } from '@/lib/strings'

const IMAGES_PER_PAGE = 6
const DRAG_THRESHOLD_PX = 4

function isGalleryScrollable(el: HTMLElement) {
  return el.scrollWidth > el.clientWidth
}

interface GalleryProps {
  images: string[]
  tripName: string
}

export function Gallery({ images, tripName }: GalleryProps) {
  const [activeIndex, setActiveIndex] = useState<number | null>(null)
  const [pageStart, setPageStart] = useState(0)
  const [isDragging, setIsDragging] = useState(false)
  const galleryRef = useRef<HTMLDivElement>(null)
  const closeButtonRef = useRef<HTMLButtonElement>(null)
  const previousFocusRef = useRef<HTMLElement | null>(null)
  const suppressClickRef = useRef(false)

  const close = useCallback(() => {
    setActiveIndex(null)
    previousFocusRef.current?.focus()
    previousFocusRef.current = null
  }, [])

  const goPrev = useCallback(() => {
    setActiveIndex((i) => (i === null ? null : (i - 1 + images.length) % images.length))
  }, [images.length])

  const goNext = useCallback(() => {
    setActiveIndex((i) => (i === null ? null : (i + 1) % images.length))
  }, [images.length])

  useEffect(() => {
    if (activeIndex === null) return

    previousFocusRef.current = document.activeElement as HTMLElement | null
    closeButtonRef.current?.focus()

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

  useEffect(() => {
    setPageStart(0)
  }, [images])

  useEffect(() => {
    const gallery = galleryRef.current
    if (!gallery) return

    const onWheel = (e: WheelEvent) => {
      if (!isGalleryScrollable(gallery)) return
      if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return

      e.preventDefault()
      gallery.scrollLeft += e.deltaY
    }

    gallery.addEventListener('wheel', onWheel, { passive: false })
    return () => gallery.removeEventListener('wheel', onWheel)
  }, [images])

  const onGalleryPointerDown = (e: React.PointerEvent<HTMLDivElement>) => {
    const gallery = galleryRef.current
    if (
      !gallery ||
      e.pointerType !== 'mouse' ||
      e.button !== 0 ||
      !isGalleryScrollable(gallery)
    ) {
      return
    }

    const pointerId = e.pointerId
    const startX = e.clientX
    const startScrollLeft = gallery.scrollLeft
    let moved = false

    const onPointerMove = (moveEvent: PointerEvent) => {
      if (moveEvent.pointerId !== pointerId) return

      const deltaX = moveEvent.clientX - startX
      if (!moved && Math.abs(deltaX) <= DRAG_THRESHOLD_PX) return

      if (!moved) {
        moved = true
        setIsDragging(true)
      }

      moveEvent.preventDefault()
      gallery.scrollLeft = startScrollLeft - deltaX
    }

    const onPointerEnd = (endEvent: PointerEvent) => {
      if (endEvent.pointerId !== pointerId) return

      document.removeEventListener('pointermove', onPointerMove)
      document.removeEventListener('pointerup', onPointerEnd)
      document.removeEventListener('pointercancel', onPointerEnd)

      if (moved) {
        suppressClickRef.current = true
        window.setTimeout(() => {
          suppressClickRef.current = false
        }, 0)
      }

      setIsDragging(false)
    }

    document.addEventListener('pointermove', onPointerMove)
    document.addEventListener('pointerup', onPointerEnd)
    document.addEventListener('pointercancel', onPointerEnd)
  }

  const openPhoto = (index: number) => {
    if (suppressClickRef.current) return
    setActiveIndex(index)
  }

  if (images.length === 0) return null

  const activeSrc = activeIndex !== null ? images[activeIndex] : null
  const canPaginate = images.length > IMAGES_PER_PAGE
  const maxPageStart =
    Math.floor((images.length - 1) / IMAGES_PER_PAGE) * IMAGES_PER_PAGE

  const goPrevPage = () => {
    setPageStart((start) => Math.max(0, start - IMAGES_PER_PAGE))
  }

  const goNextPage = () => {
    setPageStart((start) => Math.min(maxPageStart, start + IMAGES_PER_PAGE))
  }

  const isThumbVisibleOnDesktop = (index: number) =>
    !canPaginate || (index >= pageStart && index < pageStart + IMAGES_PER_PAGE)

  return (
    <>
      <div
        className={`gallery-carousel${canPaginate ? ' gallery-carousel--paginated' : ''}`}
      >
        {canPaginate && (
          <button
            type="button"
            className="gallery-carousel__nav gallery-carousel__nav--prev"
            onClick={goPrevPage}
            disabled={pageStart === 0}
            aria-label={ui.previousGalleryPhotos}
          >
            &#8249;
          </button>
        )}

        <div
          ref={galleryRef}
          className={`gallery${isDragging ? ' gallery--dragging' : ''}`}
          onPointerDown={onGalleryPointerDown}
        >
          {images.map((src, i) => (
            <button
              key={`${src}-${i}`}
              type="button"
              className={`gallery__thumb${
                !isThumbVisibleOnDesktop(i) ? ' gallery__thumb--desktop-hidden' : ''
              }`}
              onClick={() => openPhoto(i)}
              aria-label={ui.viewPhoto(i + 1, images.length)}
              tabIndex={isThumbVisibleOnDesktop(i) ? 0 : -1}
              aria-hidden={!isThumbVisibleOnDesktop(i)}
            >
              <img
                src={src}
                alt={imageAltFromPath(src, tripName)}
                className="gallery__img"
                loading="lazy"
                draggable={false}
              />
            </button>
          ))}
        </div>

        {canPaginate && (
          <button
            type="button"
            className="gallery-carousel__nav gallery-carousel__nav--next"
            onClick={goNextPage}
            disabled={pageStart >= maxPageStart}
            aria-label={ui.nextGalleryPhotos}
          >
            &#8250;
          </button>
        )}
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
              ref={closeButtonRef}
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
