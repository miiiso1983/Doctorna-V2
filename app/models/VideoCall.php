<?php
/**
 * VideoCall Model
 * Represents a video call session linked to an appointment
 */

require_once APP_PATH . '/core/Model.php';

class VideoCall extends Model {
    protected $table = 'video_calls';
    protected $fillable = [
        'appointment_id', 'room_code', 'status', 'started_at', 'ended_at', 'created_by_user_id'
    ];

    public function createRoom($appointmentId, $createdByUserId, $roomCode) {
        return $this->create([
            'appointment_id' => (int)$appointmentId,
            'room_code' => $roomCode,
            'status' => 'scheduled',
            'created_by_user_id' => (int)$createdByUserId
        ]);
    }

    public function findByAppointment($appointmentId) {
        $rows = $this->where('appointment_id = :a', ['a' => (int)$appointmentId]);
        return $rows[0] ?? null;
    }

    public function markStarted($id) {
        return $this->update($id, [
            'status' => 'ongoing',
            'started_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markEnded($id) {
        return $this->update($id, [
            'status' => 'ended',
            'ended_at' => date('Y-m-d H:i:s')
        ]);
    }
}

