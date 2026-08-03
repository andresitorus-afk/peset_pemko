// ponytail: planar shoelace, akurat untuk luas aset kota; pakai turf.geodesy bila butuh presisi global
export function polygonAreaM2(geo: any): number {
  if (!geo || !geo.coordinates) return 0
  const lat0 = 3.585 // Medan
  const mPerLat = 110540
  const mPerLng = 111320 * Math.cos((lat0 * Math.PI) / 180)
  const toXY = ([lng, lat]: number[]) => [lng * mPerLng, lat * mPerLat] as const
  const ringArea = (ring: number[][]) => {
    let s = 0
    for (let i = 0; i < ring.length; i++) {
      const [x1, y1] = toXY(ring[i])
      const [x2, y2] = toXY(ring[(i + 1) % ring.length])
      s += x1 * y2 - x2 * y1
    }
    return s / 2
  }
  const rings = geo.type === 'Polygon' ? [geo.coordinates] : geo.type === 'MultiPolygon' ? geo.coordinates : []
  return Math.abs(rings.reduce((t, poly) => t + poly.reduce((a, ring) => a + ringArea(ring), 0), 0))
}
