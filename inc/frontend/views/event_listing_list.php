<?php
use MZ_Mindbody as NS;
use MZ_Mindbody\Inc\Core as Core;

if ((is_array($data->events)) && !empty($data->events)): ?>
    <table class="mz_event_list_listing">

    <?php foreach ($data->events as $date => $events): ?>
        <?php foreach ($events as $event): ?>
        <tr>
            <td><?echo date_i18n(Core\MZ_Mindbody_Api::$date_format, strtotime($event->StartDateTime)); ?></td>
            <td>
                <?echo date_i18n(Core\MZ_Mindbody_Api::$time_format, strtotime($event->StartDateTime)); ?> -
                <?echo date_i18n(Core\MZ_Mindbody_Api::$time_format, strtotime($event->EndDateTime)); ?>
            </td>
            <td>
                <?php echo $event->ClassName; ?> with <?php echo $event->StaffName; ?>

            </td>
        </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </table>
<?php elseif (count($data->events) == 0 ): ?>
    <h4><?php echo $data->no_events; ?></h4>
<?php else: ?>
    <div class="error"><?php _e('Error Retrieving Events', 'mz_mindbody_api'); ?></div>
    <p><?php var_dump( $data->events ); ?></p>

<?php endif; ?>