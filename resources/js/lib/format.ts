export function formatDate(input: string | Date | null | undefined): string {
  if (!input) return '-';

  if (input instanceof Date) {
    if (isNaN(input.getTime())) return '-';
    const dd = String(input.getDate()).padStart(2, '0');
    const mm = String(input.getMonth() + 1).padStart(2, '0');
    const yyyy = input.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
  }

  // If it's a string, prefer parsing stable YYYY-MM-DD to avoid timezone shifts
  const m = input.match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (m) {
    const [, y, mo, d] = m;
    return `${d}/${mo}/${y}`;
  }

  // Fallback to Date parsing for other formats
  const dt = new Date(input);
  if (isNaN(dt.getTime())) return String(input);
  const dd = String(dt.getDate()).padStart(2, '0');
  const mm = String(dt.getMonth() + 1).padStart(2, '0');
  const yyyy = dt.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

export function formatCurrency(
  value: number | string | null | undefined,
  currency: string = 'USD',
  locale: string = 'en-US'
): string {
  if (value === null || value === undefined || value === '') return '-';
  let num: number;
  if (typeof value === 'string') {
    // Remove any non-numeric except dot and minus
    const cleaned = value.replace(/[^0-9.-]/g, '');
    num = Number.parseFloat(cleaned);
  } else {
    num = value;
  }
  if (!Number.isFinite(num)) return String(value);
  try {
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency,
      currencyDisplay: 'symbol',
      maximumFractionDigits: 2,
      minimumFractionDigits: 2,
    }).format(num);
  } catch {
    // Fallback simple formatting
    return `${currency} ${num.toFixed(2)}`;
  }
}

interface Stay {
  start_date: string;
  end_date: string;
}

export function getStayStatus(stay: Stay): 'active' | 'past' | 'future' {
  const now = new Date();
  const startDate = new Date(stay.start_date);
  const endDate = new Date(stay.end_date);
  
  if (startDate <= now && endDate >= now) {
    return 'active';
  } else if (endDate < now) {
    return 'past';
  } else {
    return 'future';
  }
}
