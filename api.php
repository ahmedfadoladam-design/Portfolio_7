<?php
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__ . '/data/bookings.json';
if (!file_exists($file)) file_put_contents($file, '[]', LOCK_EX);
function reply($success, $message, $code=200, $data=null) { http_response_code($code); echo json_encode(['success'=>$success,'message'=>$message,'data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function clean($key) { return trim(filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS) ?? ''); }
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
  $name = clean('customer_name'); $phone = clean('phone'); $date = clean('booking_date'); $time = clean('booking_time'); $field = clean('field_name'); $fieldId = (int)($_POST['field_id'] ?? 0); $notes = clean('notes');
  if (!$name || !$phone || !$date || !$time || !$field || !$fieldId) reply(false, 'يرجى تعبئة جميع الحقول المطلوبة.', 422);
  if (!preg_match('/^05\d{8}$/', preg_replace('/\s+/', '', $phone))) reply(false, 'يرجى إدخال رقم جوال صحيح يبدأ بـ 05.', 422);
  if ($date < date('Y-m-d')) reply(false, 'لا يمكن اختيار تاريخ سابق.', 422);
  $bookings = json_decode(file_get_contents($file), true) ?: [];
  foreach ($bookings as $booking) if ($booking['field_id'] === $fieldId && $booking['booking_date'] === $date && $booking['booking_time'] === $time) reply(false, 'هذا الموعد محجوز مسبقًا، اختر وقتًا آخر.', 409);
  $booking = ['id'=>uniqid('BK-'), 'field_id'=>$fieldId, 'field_name'=>$field, 'customer_name'=>$name, 'phone'=>$phone, 'booking_date'=>$date, 'booking_time'=>$time, 'notes'=>$notes, 'status'=>'مؤكد', 'created_at'=>date('Y-m-d H:i:s')];
  $bookings[] = $booking; file_put_contents($file, json_encode($bookings, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), LOCK_EX);
  reply(true, 'تم تأكيد حجزك بنجاح! نتمنى لك مباراة ممتعة.', 201, $booking);
}
if ($method === 'GET') { $bookings = json_decode(file_get_contents($file), true) ?: []; reply(true, 'تم جلب الحجوزات.', 200, array_reverse($bookings)); }
if ($method === 'DELETE') { parse_str(file_get_contents('php://input'), $input); $id = $input['id'] ?? ''; $bookings = json_decode(file_get_contents($file), true) ?: []; $new = array_values(array_filter($bookings, fn($b) => $b['id'] !== $id)); if(count($new) === count($bookings)) reply(false,'الحجز غير موجود.',404); file_put_contents($file,json_encode($new,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX); reply(true,'تم إلغاء الحجز.'); }
reply(false, 'الطلب غير مدعوم.', 405);
?>
