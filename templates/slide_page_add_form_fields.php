<div class="form-field">
    <label for="title_color"><?= _e('Title color', $this->translate_key) ?></label>
    <input type="text" name="slide_page_meta[title_color]" id="title_color" value="#F5C949" class="uhsp-colorpicker">
    <p><?= _e('Set a hexadecimal color code for the title text and the hover bar.', $this->translate_key) ?></p>
</div>
<div class="form-field">
    <label for="subtitle_color"><?= _e('Subtitle color', $this->translate_key) ?></label>
    <input type="text" name="slide_page_meta[subtitle_color]" id="subtitle_color" value="#FFFFFF" class="uhsp-colorpicker">
    <p><?= _e('Set a hexadecimal color code for the subtitle text.', $this->translate_key) ?></p>
</div>
<div class="form-field">
    <label for="overlay_color"><?= _e('Overlay color', $this->translate_key) ?></label>
    <input type="text" name="slide_page_meta[overlay_color]" id="overlay_color" value="#334D5C" class="uhsp-colorpicker">
    <p><?= _e('Set a hexadecimal color code for the overlay that covers the slider.', $this->translate_key) ?></p>
</div>
<div class="form-field">
    <label for="overlay_opacity"><?= _e('Overlay opacity', $this->translate_key) ?></label>
    <input type="text" name="slide_page_meta[overlay_opacity]" id="overlay_opacity" value="75%">
    <p><?= _e('Set the transparancy value of the overlay. If you want to disable the overlay you can set this value to 0%.', $this->translate_key) ?></p>
</div>
<div class="form-field">
    <label for="arrow_buttons"><?= _e('Arrow buttons', $this->translate_key) ?></label>
    <input type="checkbox" name="slide_page_meta[arrow_buttons]" id="arrow_buttons">
    <p><?= _e('Check the box if you want to activate the navigation arrows on the left and right side of the slider.', $this->translate_key) ?></p>
</div>
