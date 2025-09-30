<?php
/**
 * Realtime event publisher (provider-agnostic scaffold)
 *
 * Drivers supported (scaffold):
 * - none   : no-op (fallback to polling)
 * - pusher : TODO (requires credentials)
 * - ably   : TODO (requires credentials)
 * - native : TODO (self-hosted WS bridge)
 *
 * Usage:
 *   (new Realtime())->publish($channel, $event, $payloadArray);
 */
class Realtime {
    private string $driver;

    public function __construct() {
        $this->driver = defined('REALTIME_DRIVER') ? REALTIME_DRIVER : 'none';
    }

    /**
     * Publish an event to a channel.
     * @param string $channel e.g., "user-123" or "appointment-45"
     * @param string $event   e.g., "chat_message", "video_room", "video_status", "webrtc_signal"
     * @param array  $data    payload
     * @return bool
     */
    public function publish(string $channel, string $event, array $data = []): bool {
        try {
            switch ($this->driver) {
                case 'pusher':
                    // TODO: Implement Pusher REST publish using credentials from env
                    // Example endpoint: https://api-<cluster>.pusher.com/apps/<app_id>/events
                    // Requires: app_id, key, secret, cluster
                    return false;
                case 'ably':
                    // TODO: Implement Ably REST publish
                    return false;
                case 'native':
                    // TODO: Implement native/self-hosted publish (e.g., HTTP bridge to WS server)
                    return false;
                case 'none':
                default:
                    // No-op (polling fallback)
                    return true;
            }
        } catch (\Throwable $e) {
            // Log and continue
            error_log('[Realtime] publish failed: ' . $e->getMessage());
            return false;
        }
    }
}

