<?php
debug_backtrace() || die ('Direct access not permitted');

$max_adults_search = 30;
$max_children_search = 10;

if(!isset($_SESSION['destination_id'])) $_SESSION['destination_id'] = 0;
if(!isset($destination_name)) $destination_name = '';

if(!isset($_SESSION['num_adults']))
    $_SESSION['num_adults'] = (isset($_SESSION['book']['adults'])) ? $_SESSION['book']['adults'] : 1;
if(!isset($_SESSION['num_children']))
    $_SESSION['num_children'] = (isset($_SESSION['book']['children'])) ? $_SESSION['book']['children'] : 0;

$from_date = (isset($_SESSION['from_date'])) ? $_SESSION['from_date'] : '';
$to_date = (isset($_SESSION['to_date'])) ? $_SESSION['to_date'] : ''; ?>

<!-- Booking Form -->
<form action="<?php echo DOCBASE.$pms_sys_pages['booking']['alias']; ?>" method="post" class="booking-search p-4 shadow rounded bg-light">
    <?php if(isset($room_id)) { ?>
        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
    <?php } ?>

    <div class="row text-white">
        <!-- Check-in Date -->
        <div class="col-md-3">
            <div class="form-group">
                <label  style="color: white;" ><i class="fas fa-calendar-alt"></i> <?php echo $pms_texts['CHECK_IN']; ?></label>
                <input type="text" class="form-control datepicker" id="from_picker" name="from_date" value="<?php echo $from_date; ?>" placeholder="Check-in Date">
            </div>
        </div>

        <!-- Check-out Date -->
        <div class="col-md-3">
            <div class="form-group">
                <label  style="color: white;"><i class="fas fa-calendar-alt"></i> <?php echo $pms_texts['CHECK_OUT']; ?></label>
                <input type="text" class="form-control datepicker" id="to_picker" name="to_date" value="<?php echo $to_date; ?>" placeholder="Check-out Date">
            </div>
        </div>

        <!-- Adults Selection -->
        <div class="col-md-2">
            <div class="form-group">
                <label  style="color: white;"><i class="fas fa-user"></i> <?php echo $pms_texts['ADULTS']; ?></label>
                <select name="num_adults" class="form-control">
                    <?php for($i = 1; $i <= $max_adults_search; $i++) {
                        $selected = ($_SESSION['num_adults'] == $i) ? 'selected' : '';
                        echo "<option value='$i' $selected>$i</option>";
                    } ?>
                </select>
            </div>
        </div>

        <!-- Children Selection -->
        <div class="col-md-2">
            <div class="form-group">
                <label  style="color: white;"><i class="fas fa-child"></i> <?php echo $pms_texts['CHILDREN']; ?></label>
                <select name="num_children" class="selectpicker form-control">
                        <?php
                        for($i = 0; $i <= $max_children_search; $i++){
                            $select = ($_SESSION['num_children'] == $i) ? ' selected="selected"' : '';
                            echo '<option value="'.$i.'"'.$select.'>'.$i.'</option>';
                        } ?>
                    </select>
            </div>
        </div>

        <!-- Search Button -->
        <div class="col-md-2 d-flex align-items-end">
            <button style="margin-top: 26px;" class="btn btn-primary btn-block" type="submit" name="check_availabilities">
                <i class="fas fa-search"></i> <?php echo $pms_texts['CHECK']; ?>
            </button>
        </div>
    </div>
</form>

