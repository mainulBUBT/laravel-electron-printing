<?php

namespace LaravelElectronPrinting\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintJobCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobId;
    public $html;
    public $printerName;
    public $options;

    /**
     * Create a new event instance.
     *
     * @param string $html
     * @param string|null $printerName
     * @param array $options
     * @param string|null $jobId
     */
    public function __construct($html, $printerName = null, $options = [], $jobId = null)
    {
        $this->html = $html;
        $this->printerName = $printerName;
        $this->options = $options;
        $this->jobId = $jobId ?? uniqid('print_', true);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel(config('electron-printing.broadcast_channel', 'printing'));
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'print.job';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'jobId' => $this->jobId,
            'html' => $this->html,
            'printerName' => $this->printerName,
            'options' => $this->options,
            'timestamp' => now()->toIso8601String()
        ];
    }
}
