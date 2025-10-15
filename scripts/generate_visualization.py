
#!/usr/bin/env python3
"""
Wikipedia Table Visualization Generator

This script generates visualizations from Wikipedia table data.
It accepts JSON input data containing labels and values, and outputs a PNG image.

Usage:
    python generate_visualization.py --input /path/to/input.json --output /path/to/output.png

Input JSON format:
{
    "labels": ["Label1", "Label2", ...],
    "values": [value1, value2, ...],
    "label_column": "Column name for labels",
    "numeric_column": "Column name for values",
    "title": "Chart title"
}

Dependencies:
    - matplotlib
    - seaborn
    - pandas
    - argparse
    - json
"""

import argparse
import json
import os
import sys
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
import numpy as np
from matplotlib.ticker import MaxNLocator

def parse_arguments():
    """Parse command line arguments."""
    parser = argparse.ArgumentParser(description='Generate visualization from Wikipedia table data')
    parser.add_argument('--input', required=True, help='Path to input JSON file')
    parser.add_argument('--output', required=True, help='Path to output PNG file')
    parser.add_argument('--type', default='bar', choices=['bar', 'line', 'scatter'],
                        help='Type of visualization to generate')
    parser.add_argument('--dpi', type=int, default=100, help='DPI for output image')
    return parser.parse_args()

def load_data(input_path):
    """Load data from input JSON file."""
    try:
        with open(input_path, 'r') as f:
            data = json.load(f)

        # Validate required fields
        required_fields = ['labels', 'values', 'label_column', 'numeric_column']
        for field in required_fields:
            if field not in data:
                raise ValueError(f"Missing required field: {field}")

        # Ensure labels and values have the same length
        if len(data['labels']) != len(data['values']):
            raise ValueError("Labels and values must have the same length")

        return data
    except Exception as e:
        print(f"Error loading data: {str(e)}", file=sys.stderr)
        sys.exit(1)

def determine_best_chart_type(data):
    """Determine the best chart type based on the data."""
    # If we have more than 20 data points, line chart might be better
    if len(data['labels']) > 20:
        return 'line'

    # Check if labels look like years or dates (time series data)
    if all(label.isdigit() and 1900 <= int(label) <= 2100 for label in data['labels']):
        return 'line'  # Years are good for line charts

    # Default to bar chart
    return 'bar'

def create_visualization(data, chart_type=None):
    """Create visualization based on the data and chart type."""
    # Set up the style
    sns.set_style("whitegrid")
    plt.figure(figsize=(12, 8))

    # Create DataFrame from the data
    df = pd.DataFrame({
        data['label_column']: data['labels'],
        data['numeric_column']: data['values']
    })

    # Determine best chart type if not specified
    if chart_type is None or chart_type == 'auto':
        chart_type = determine_best_chart_type(data)

    # Set title and labels
    title = data.get('title', f"{data['numeric_column']} by {data['label_column']}")
    plt.title(title, fontsize=16, pad=20)
    plt.xlabel(data['label_column'], fontsize=12)
    plt.ylabel(data['numeric_column'], fontsize=12)

    # Create the appropriate chart
    if chart_type == 'line':
        ax = sns.lineplot(x=data['label_column'], y=data['numeric_column'], data=df, marker='o')
    elif chart_type == 'scatter':
        ax = sns.scatterplot(x=data['label_column'], y=data['numeric_column'], data=df, s=100)
    else:  # Default to bar chart
        ax = sns.barplot(x=data['label_column'], y=data['numeric_column'], data=df)

    # Format the axes
    if len(data['labels']) > 10:
        plt.xticks(rotation=45, ha='right')

    # Add value labels on bars for bar charts with fewer data points
    if chart_type == 'bar' and len(data['labels']) <= 15:
        for i, v in enumerate(data['values']):
            ax.text(i, v + max(data['values']) * 0.01, f"{v:.1f}", ha='center')

    # Ensure y-axis uses whole numbers if appropriate
    if all(isinstance(v, int) or (isinstance(v, float) and v.is_integer()) for v in data['values']):
        ax.yaxis.set_major_locator(MaxNLocator(integer=True))

    # Adjust layout
    plt.tight_layout()

    return plt

def save_visualization(plt, output_path, dpi=100):
    """Save the visualization to the output path."""
    try:
        # Ensure the output directory exists
        output_dir = os.path.dirname(output_path)
        if output_dir and not os.path.exists(output_dir):
            os.makedirs(output_dir, exist_ok=True)

        # Save the figure
        plt.savefig(output_path, dpi=dpi, bbox_inches='tight')
        plt.close()

        print(f"Visualization saved to {output_path}")
        return True
    except Exception as e:
        print(f"Error saving visualization: {str(e)}", file=sys.stderr)
        return False

def main():
    """Main function to run the script."""
    args = parse_arguments()
    data = load_data(args.input)
    plt = create_visualization(data, args.type)
    success = save_visualization(plt, args.output, args.dpi)
    sys.exit(0 if success else 1)

if __name__ == "__main__":
    main()
