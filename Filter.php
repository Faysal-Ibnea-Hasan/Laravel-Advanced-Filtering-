<?php
namespace App\Filters;
class BlogFilter extends QueryFilter
{
    public function search($keyword)
    {
        return $this->builder
            ->where('title', 'LIKE', '%' . $keyword . '%')
            ->orWhere('content', 'LIKE', '%' . $keyword . '%');
    }
    public function category($id)
    {
        return $this->builder->whereHas('categories', function ($query) use ($id) {
            $query->where('categories.id', $id);
        });
    }

    public function tag($id)
    {
        return $this->builder->whereHas('tags', function ($query) use ($id) {
            $query->where('tags.id', $id);
        });
    }
    public function author($keyword)
    {
        return $this->builder->whereHas('users', function ($query) use ($keyword) {
            $query->where('users.name', 'LIKE', '%' . $keyword . '%');
        });
    }
    // Basic date range
    // /blog?dateRange=2024-03-01,2024-03-31

    // Separate from/to dates
    // /blog?from=2024-03-01&to=2024-03-31
    public function dateRange($range)
    {
        try {
            // If single date is provided
            if (!str_contains($range, ',')) {
                return $this->builder->whereDate('created_at', date('Y-m-d', strtotime($range)));
            }

            [$from, $to] = explode(',', $range);

            // Convert and validate dates
            $fromDate = date('Y-m-d', strtotime($from));
            $toDate = date('Y-m-d', strtotime($to));

            if ($fromDate && $toDate) {
                return $this->builder->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            }

            return $this->builder;
        } catch (\Exception $e) {
            return $this->builder; // Return unmodified query if dates are invalid
        }
    }

    // Optional: Add specific time filtering
    // With time
    // /blog?dateTimeRange=2024-03-01 09:00:00,2024-03-31 17:00:00
    public function dateTimeRange($range)
    {
        try {
            [$from, $to] = explode(',', $range);

            return $this->builder->whereBetween('created_at', [
                date('Y-m-d H:i:s', strtotime($from)),
                date('Y-m-d H:i:s', strtotime($to)),
            ]);
        } catch (\Exception $e) {
            return $this->builder;
        }
    }

    // Filter by specific month and year
    // By month and year
    // /blog?month=3&year=2024
    public function month($month)
    {
        return $this->builder->whereMonth('created_at', $month);
    }

    public function year($year)
    {
        return $this->builder->whereYear('created_at', $year);
    }

    // Filter by relative dates
    // By period
    // /blog?period=this_week
    // /blog?period=last_month
    public function period($period)
    {
        switch ($period) {
            case 'today':
                return $this->builder->whereDate('created_at', today());
            case 'yesterday':
                return $this->builder->whereDate('created_at', today()->subDay());
            case 'this_week':
                return $this->builder->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
            case 'last_week':
                return $this->builder->whereBetween('created_at', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ]);
            case 'this_month':
                return $this->builder->whereMonth('created_at', now()->month);
            case 'last_month':
                return $this->builder->whereMonth('created_at', now()->subMonth()->month);
            default:
                return $this->builder;
        }
    }
}
