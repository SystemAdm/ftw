import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(...inputs))
}

// Accepts strings, Wayfinder route objects ({ url, method }), or objects with an href
export function toUrl(input: unknown): string {
  if (typeof input === 'string') {
    return input
  }

  if (input && typeof input === 'object') {
    const anyInput = input as Record<string, unknown>
    if (typeof anyInput.url === 'string') {
      return anyInput.url as string
    }
    if (typeof anyInput.href === 'string') {
      return anyInput.href as string
    }
  }

  return String(input ?? '')
}

function normalizePath(url: string): string {
  try {
    const base = typeof window !== 'undefined' && window.location ? window.location.origin : 'http://localhost'
    const u = new URL(url, base)
    let path = u.pathname
    // Remove trailing slash except for root
    if (path.length > 1 && path.endsWith('/')) {
      path = path.slice(0, -1)
    }
    return path
  } catch {
    // Fallback: ensure leading slash and strip trailing slash
    let path = url || ''
    if (!path.startsWith('/')) {
      path = '/' + path
    }
    if (path.length > 1 && path.endsWith('/')) {
      path = path.slice(0, -1)
    }
    return path
  }
}

// Checks if currentUrl matches or is within targetUrl path
export function urlIsActive(targetUrl: string, currentUrl: string): boolean {
  const target = normalizePath(toUrl(targetUrl))
  const current = normalizePath(toUrl(currentUrl))

  if (target === '/') {
    return current === '/'
  }

  return current === target || current.startsWith(target + '/')
}
