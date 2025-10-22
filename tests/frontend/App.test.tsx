import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import App from '../../resources/js/App'

describe('App Component', () => {
  beforeEach(() => {
    global.fetch = vi.fn()
  })

  it('renders Wikipedia Table Visualizer heading', () => {
    render(<App />)
    expect(screen.getByText('Wikipedia Table Visualizer')).toBeInTheDocument()
  })

  it('renders URL input field', () => {
    render(<App />)
    const input = screen.getByPlaceholderText(/https:\/\/en.wikipedia.org\/wiki\//i)
    expect(input).toBeInTheDocument()
  })

  it('renders Extract Table Data button', () => {
    render(<App />)
    const button = screen.getByRole('button', { name: /extract table data/i })
    expect(button).toBeInTheDocument()
  })

  it('shows error when URL is empty on submit', async () => {
    render(<App />)
    const button = screen.getByRole('button', { name: /extract table data/i })
    
    fireEvent.click(button)
    
    await waitFor(() => {
      const input = screen.getByPlaceholderText(/https:\/\/en.wikipedia.org\/wiki\//i)
      expect(input).toBeRequired()
    })
  })

  it('displays loading state during API request', async () => {
    const mockFetch = vi.fn(() => 
      new Promise(resolve => setTimeout(() => resolve({
        ok: true,
        json: async () => ({
          headers: ['Country', 'Population'],
          rows: [['China', '1400000000']],
          numericColumns: ['Population']
        })
      }), 100))
    )
    global.fetch = mockFetch

    render(<App />)
    const input = screen.getByPlaceholderText(/https:\/\/en.wikipedia.org\/wiki\//i)
    const button = screen.getByRole('button', { name: /extract table data/i })

    fireEvent.change(input, { 
      target: { value: 'https://en.wikipedia.org/wiki/Test' } 
    })
    fireEvent.click(button)

    expect(button).toHaveTextContent(/extracting data/i)
  })

  it('displays table data after successful extraction', async () => {
    const mockData = {
      headers: ['Country', 'Population'],
      rows: [
        ['China', '1411778724'],
        ['India', '1393409038']
      ],
      numericColumns: ['Population']
    }

    global.fetch = vi.fn(() =>
      Promise.resolve({
        ok: true,
        json: async () => mockData,
      })
    )

    render(<App />)
    const input = screen.getByPlaceholderText(/https:\/\/en.wikipedia.org\/wiki\//i)
    const button = screen.getByRole('button', { name: /extract table data/i })

    fireEvent.change(input, { 
      target: { value: 'https://en.wikipedia.org/wiki/List_of_countries' } 
    })
    fireEvent.click(button)

    await waitFor(() => {
      expect(screen.getByText('China')).toBeInTheDocument()
      expect(screen.getByText('India')).toBeInTheDocument()
    })
  })

  it('displays error message when API fails', async () => {
    global.fetch = vi.fn(() =>
      Promise.resolve({
        ok: false,
        status: 500,
      })
    )

    render(<App />)
    const input = screen.getByPlaceholderText(/https:\/\/en.wikipedia.org\/wiki\//i)
    const button = screen.getByRole('button', { name: /extract table data/i })

    fireEvent.change(input, { 
      target: { value: 'https://en.wikipedia.org/wiki/Test' } 
    })
    fireEvent.click(button)

    await waitFor(() => {
      expect(screen.getByText(/failed to fetch/i)).toBeInTheDocument()
    })
  })
})
