<?php
/**
 * Payment Model
 * Stores payment attempts and results for appointments
 */

require_once APP_PATH . '/core/Model.php';

class Payment extends Model {
    protected $table = 'payments';
    protected $fillable = [
        'appointment_id', 'patient_id', 'doctor_id', 'amount', 'currency',
        'status', 'gateway', 'gateway_ref', 'auth_code', 'extra'
    ];

    public function createPayment($data) {
        $data['status'] = $data['status'] ?? 'initiated';
        $data['currency'] = $data['currency'] ?? 'IQD';
        $data['gateway'] = $data['gateway'] ?? 'qi_card';
        return $this->create($data);
    }

    public function markPaid($paymentId, $gatewayRef = null, $authCode = null, $extra = null) {
        return $this->update($paymentId, [
            'status' => 'paid',
            'gateway_ref' => $gatewayRef,
            'auth_code' => $authCode,
            'extra' => $extra
        ]);
    }

    public function markFailed($paymentId, $reason = null, $extra = null) {
        return $this->update($paymentId, [
            'status' => 'failed',
            'extra' => $extra ?? $reason
        ]);
    }

    public function findByGatewayRef($gatewayRef) {
        $rows = $this->where('gateway_ref = :ref', ['ref' => $gatewayRef]);
        return $rows[0] ?? null;
    }

    public function findLatestForAppointment($appointmentId) {
        $sql = "SELECT * FROM {$this->table} WHERE appointment_id = :a ORDER BY id DESC LIMIT 1";
        $rows = $this->fetchRaw($sql, ['a' => (int)$appointmentId]);
        return $rows[0] ?? null;
    }
}

