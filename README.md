![Unique Hover Slider Plus](http://i.imgur.com/SRGm08M.png "Unique Hover Slider Plus")

# Less or More WP Plugins // Unique Hover Slider Plus

A cool slider with unique hover functionality that you won't find anywhere else!


## Requirements

- PHP 5.4+

## Usage

In order to use the slider plugin, you will have to create a slider and attach a set of slides to it. **Sliders are limited to 5 slides**; this is required for the stylistic display of the slide titles. Adding any more would clutter the slider too much and overflow the slide titles. If you are looking to support more slides, this slider plugin is simply not the on you are looking for.

### Add or Edit Sliders

To create a new slider, go to the "Add or Edit Sliders" submenu in the "UHSP Slider" option. Creating a new slider gives you several options, primarily the default Wordpress taxonomy settings such as "Name", "Slug", "Parent" and "Description". Take note that these aren't actually used by the plugin itself, but you can use them for yourself to mark which slider does what. The slider configuration can be found below these options:

| Option Name              | Effect                                                                |
|--------------------------|-----------------------------------------------------------------------|
| Title color              | Decides the color of the slide main titles. (Largest text)            |
| Subtitle color           | Decides the color of the slide subtitles. (Smaller text)              |
| Overlay color            | Decides the color of the overlay that covers all slide backgrounds.   |
| Overlay opacity          | Decides the opacity of the overlay that covers all slide backgrounds. |
| Arrow buttons            | Check the box to enable slide navigation with previous / next arrows. |

### Add New Slide

After creating your slider you'll have to add some slides to it. For this plugin, we have a custom post type called "slide" which contains the options to populate the slide:

| Option Name              | Effect                                                                |
|--------------------------|-----------------------------------------------------------------------|
| Title                    | The title of the slide. (Largest text)                                |
| Description              | The subtitle of the slide. (Smaller text)                             |
| Featured Image           | The background image of the slide.                                    |
| Foreground Icon          | The foreground icon position in the center of the slide.              |
| Order                    | The position the slide should have within the slider.                 |
| Sliders                  | The sliders that this slide should appear in.                         |

## Style

### Fonts

If you want to change the size of the fonts in the slider, you can change the CSS manually.

The slider titles:
```css
.uhsp-slider-wrapper .uhsp-slide-title {
    font-size: 30px;
}
```

The slider subtitles / descriptions:
```css
.uhsp-slider-wrapper .uhsp-slide-subtitle {
    font-size: 12px;
}
```

The slider uses the same font as the theme you use, but if you like you can add your own font here.
```css
.uhsp-slider-wrapper .uhsp-slide-title,
.uhsp-slider-wrapper .uhsp-slide-subtitle {
    font-family: "Your Font", Helvetica, sans-serif;
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.
