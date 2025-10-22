import { useState } from 'react'

interface TableColumn {
  name: string
  isNumeric: boolean
}

interface TableData {
  headers: string[]
  rows: (string | number)[][]
  numericColumns: string[]
}

interface VisualizationResponse {
  id: number
  imageUrl: string
  chartType: string
}

export default function App() {
  const [url, setUrl] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [tableData, setTableData] = useState<TableData | null>(null)
  const [selectedColumns, setSelectedColumns] = useState<string[]>([])
  const [chartType, setChartType] = useState('bar')
  const [visualization, setVisualization] = useState<VisualizationResponse | null>(null)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    setTableData(null)
    setVisualization(null)

    try {
      const response = await fetch('/api/extract-table', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ url })
      })

      if (!response.ok) {
        throw new Error('Failed to fetch Wikipedia data')
      }

      const data = await response.json()
      setTableData(data)
      if (data.numericColumns.length > 0) {
        setSelectedColumns([data.numericColumns[0]])
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  const handleGenerateVisualization = async () => {
    if (!tableData || selectedColumns.length === 0) return

    setError('')
    setLoading(true)

    try {
      const response = await fetch('/api/generate-visualization', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          tableData,
          selectedColumns,
          chartType
        })
      })

      if (!response.ok) {
        throw new Error('Failed to generate visualization')
      }

      const data = await response.json()
      setVisualization(data)
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="bg-blue-800 text-white p-4 shadow-lg">
        <div className="container mx-auto">
          <h1 className="text-2xl font-bold">Wikipedia Table Visualizer</h1>
        </div>
      </header>

      <main className="container mx-auto px-4 py-8">
        <div className="max-w-4xl mx-auto">
          <form onSubmit={handleSubmit} className="bg-white p-6 rounded-lg shadow-md mb-6">
            <div className="mb-4">
              <label htmlFor="url" className="block text-sm font-medium text-gray-700 mb-2">
                Wikipedia Page URL
              </label>
              <input
                type="url"
                id="url"
                value={url}
                onChange={(e) => setUrl(e.target.value)}
                placeholder="https://en.wikipedia.org/wiki/..."
                className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:bg-gray-400 transition"
            >
              {loading ? 'Extracting Data...' : 'Extract Table Data'}
            </button>
          </form>

          {error && (
            <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
              {error}
            </div>
          )}

          {tableData && (
            <div className="bg-white p-6 rounded-lg shadow-md mb-6">
              <h2 className="text-xl font-semibold mb-4">Extracted Table Data</h2>
              
              <div className="overflow-x-auto mb-6">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      {tableData.headers.map((header, idx) => (
                        <th key={idx} className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          {header}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {tableData.rows.slice(0, 10).map((row, rowIdx) => (
                      <tr key={rowIdx}>
                        {row.map((cell, cellIdx) => (
                          <td key={cellIdx} className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {cell}
                          </td>
                        ))}
                      </tr>
                    ))}
                  </tbody>
                </table>
                {tableData.rows.length > 10 && (
                  <p className="text-sm text-gray-500 mt-2">Showing 10 of {tableData.rows.length} rows</p>
                )}
              </div>

              <div className="border-t pt-6">
                <h3 className="text-lg font-semibold mb-4">Generate Visualization</h3>
                
                <div className="grid md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Select Column
                    </label>
                    <select
                      value={selectedColumns[0] || ''}
                      onChange={(e) => setSelectedColumns([e.target.value])}
                      className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                    >
                      {tableData.numericColumns.map((col) => (
                        <option key={col} value={col}>{col}</option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Chart Type
                    </label>
                    <select
                      value={chartType}
                      onChange={(e) => setChartType(e.target.value)}
                      className="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="bar">Bar Chart</option>
                      <option value="line">Line Chart</option>
                      <option value="scatter">Scatter Plot</option>
                    </select>
                  </div>
                </div>

                <button
                  onClick={handleGenerateVisualization}
                  disabled={loading || selectedColumns.length === 0}
                  className="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 disabled:bg-gray-400 transition"
                >
                  {loading ? 'Generating...' : 'Generate Visualization'}
                </button>
              </div>
            </div>
          )}

          {visualization && (
            <div className="bg-white p-6 rounded-lg shadow-md">
              <h2 className="text-xl font-semibold mb-4">Visualization</h2>
              <img 
                src={visualization.imageUrl} 
                alt="Data Visualization" 
                className="w-full rounded-lg shadow-lg"
              />
            </div>
          )}
        </div>
      </main>

      <footer className="bg-gray-200 text-center p-4 text-sm text-gray-600 mt-12">
        &copy; {new Date().getFullYear()} Wikipedia Table Visualizer
      </footer>
    </div>
  )
}
