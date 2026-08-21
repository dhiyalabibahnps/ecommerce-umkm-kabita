import type { CourierCode } from '@/types/enums';

export interface CourierService {
  serviceCode: string;
  serviceName: string;
  etd: string;
  cost: number;
}

export interface CourierMaster {
  code: CourierCode;
  name: string;
  services: CourierService[];
}

export const COURIER_MASTER_LIST: CourierMaster[] = [
  {
    code: 'JNE',
    name: 'JNE',
    services: [
      { serviceCode: 'REG', serviceName: 'Regular', etd: '2-3 hari', cost: 15000 },
      { serviceCode: 'YES', serviceName: 'Yakin Esok Sampai (Express)', etd: '1 hari', cost: 24000 },
    ],
  },
  {
    code: 'JNT',
    name: 'J&T Express',
    services: [
      { serviceCode: 'EZ', serviceName: 'Regular (EZ)', etd: '2-3 hari', cost: 14000 },
      { serviceCode: 'SUPER', serviceName: 'Super Express', etd: '1-2 hari', cost: 22000 },
    ],
  },
  {
    code: 'SICEPAT',
    name: 'SiCepat',
    services: [
      { serviceCode: 'SIUNT', serviceName: 'SiUntung Regular', etd: '2-3 hari', cost: 13000 },
      { serviceCode: 'BEST', serviceName: 'Besok Sampai Tujuan (Express)', etd: '1 hari', cost: 23000 },
    ],
  },
  {
    code: 'ANTERAJA',
    name: 'AnterAja',
    services: [
      { serviceCode: 'REG', serviceName: 'Regular Service', etd: '2-3 hari', cost: 13500 },
      { serviceCode: 'NDS', serviceName: 'Next Day Service', etd: '1 hari', cost: 21000 },
    ],
  },
  {
    code: 'POS',
    name: 'POS Indonesia',
    services: [
      { serviceCode: 'KILAT', serviceName: 'Pos Kilat Khusus', etd: '2-4 hari', cost: 12000 },
      { serviceCode: 'EXPRESS', serviceName: 'Pos Express', etd: '1 hari', cost: 20000 },
    ],
  },
  {
    code: 'NINJA',
    name: 'Ninja Xpress',
    services: [
      { serviceCode: 'STD', serviceName: 'Standard Service', etd: '2-3 hari', cost: 13000 },
      { serviceCode: 'FAST', serviceName: 'Fast Delivery', etd: '1-2 hari', cost: 22000 },
    ],
  },
];

export interface FlatShippingOption {
  key: string;
  courierCode: CourierCode;
  courierName: string;
  serviceCode: string;
  serviceName: string;
  etd: string;
  cost: number;
  label: string;
  fullCourierLabel: string;
}

export const FLAT_SHIPPING_OPTIONS: FlatShippingOption[] = COURIER_MASTER_LIST.flatMap((courier) =>
  courier.services.map((srv) => ({
    key: `${courier.code}_${srv.serviceCode}`.toLowerCase(),
    courierCode: courier.code,
    courierName: courier.name,
    serviceCode: srv.serviceCode,
    serviceName: srv.serviceName,
    etd: srv.etd,
    cost: srv.cost,
    label: `${courier.name} - ${srv.serviceCode} (${srv.serviceName}) • ${srv.etd} • Rp ${srv.cost.toLocaleString('id-ID')}`,
    fullCourierLabel: `${courier.name} ${srv.serviceCode}`,
  }))
);

export function getCourierMaster(code: string): CourierMaster | undefined {
  const normalized = code.toUpperCase();
  return COURIER_MASTER_LIST.find((c) => c.code === normalized || c.name.toUpperCase() === normalized);
}

export function formatCourierDisplay(courierString?: string | null): string {
  if (!courierString) return 'Kurir Reguler';
  return courierString
    .replace(/&amp;/g, '&')
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
}

export function resolveCourierOptionValue(rawCourier?: string | null): string {
  if (!rawCourier) return FLAT_SHIPPING_OPTIONS[0]?.fullCourierLabel || 'JNE REG';
  const cleaned = formatCourierDisplay(rawCourier).trim();
  if (!cleaned) return FLAT_SHIPPING_OPTIONS[0]?.fullCourierLabel || 'JNE REG';

  // 1. Exact match with fullCourierLabel (case-insensitive)
  const exact = FLAT_SHIPPING_OPTIONS.find(
    (opt) => opt.fullCourierLabel.toLowerCase() === cleaned.toLowerCase()
  );
  if (exact) return exact.fullCourierLabel;

  // 2. Exact match with courier code + service code or key
  const codeMatch = FLAT_SHIPPING_OPTIONS.find(
    (opt) =>
      `${opt.courierCode} ${opt.serviceCode}`.toLowerCase() === cleaned.toLowerCase() ||
      opt.key === cleaned.toLowerCase()
  );
  if (codeMatch) return codeMatch.fullCourierLabel;

  // 3. Partial match (e.g. contains courier name/code and serviceCode)
  const partialMatch = FLAT_SHIPPING_OPTIONS.find((opt) => {
    const courierKeywords = [opt.courierName.toLowerCase(), opt.courierCode.toLowerCase()];
    const hasCourier = courierKeywords.some((k) => cleaned.toLowerCase().includes(k));
    const hasService = cleaned.toLowerCase().includes(opt.serviceCode.toLowerCase());
    return hasCourier && hasService;
  });
  if (partialMatch) return partialMatch.fullCourierLabel;

  // 4. Fallback to cleaned value (so custom/legacy strings are preserved)
  return cleaned;
}

export function getCourierSelectOptions(selectedCourier?: string | null): Array<{ label: string; value: string }> {
  const baseOptions = FLAT_SHIPPING_OPTIONS.map((opt) => ({
    label: `${opt.courierName} - ${opt.serviceCode} (${opt.serviceName})`,
    value: opt.fullCourierLabel,
  }));

  if (selectedCourier) {
    const resolved = resolveCourierOptionValue(selectedCourier);
    const exists = baseOptions.some((item) => item.value.toLowerCase() === resolved.toLowerCase());
    if (!exists && resolved) {
      baseOptions.unshift({
        label: `${resolved} (Pilihan Pembeli)`,
        value: resolved,
      });
    }
  }

  return baseOptions;
}
