<div class="wrap">
    <h1><?= $this->name ?></h1>
    <form name="uhsp_add_slider" method="post" action="">
        <!-- Event will be called as action in the back-end. -->
        <input type="hidden" name="event" value="uhsp_add_slider">
        <h2><?= _e("General settings", $this->translate_key) ?></h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?= _e('Some setting', $this->translate_key) ?></th>
                    <td><input class="regular-text" type="text" name="uhsp-slider-some-setting" placeholder="<?= _e('Some setting...', $this->translate_key) ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><?= _e('Some other setting', $this->translate_key) ?></th>
                    <td><input class="regular-text" type="text" name="uhsp-slider-some-other-setting" placeholder="<?= _e('Some other setting...', $this->translate_key) ?>"></td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" class="button-primary" value="<?= _e('Update settings', $this->translate_key) ?>">
        </p>
    </form>
</div>