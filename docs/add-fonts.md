---
layout: default
title: Add fonts
parent: Customizing styles
nav_order: 1
---

## Add fonts
First step, we will go to `Pilo'Press > Styles > Fonts` and add a font.  
We have 2 choices : Google Font or Custom font.  

**Example: Google Font**

Let's say we want to add Google's Roboto Font.  
We have to fill the fields as following:  
```text
Name:            Roboto
URL:             https://fonts.googleapis.com/css2?family=Roboto&display=swap
Auto-enqueue:    true
```
**NB:** The `Auto-enqueue` option will automatically add the `<link>` tag in your HTML if set to `true`.  

<figure class="video_container">
  <iframe src="https://www.loom.com/embed/edc528d329b543d0a4fe7062df7f43d2"
  frameborder="0" allowfullscreen="true" width="400" height="250"></iframe>
</figure>
* * *

**Example: Custom font**

Let's say we want to add a font named _Awesome Font_.  
_Be careful with your font formats, because of [browser compatibility](https://www.w3schools.com/css/css3_fonts.asp)._  

We have to fill the fields as following:  
```text
Name:      Awesome Font
Files:     <Your files>
Weight:    normal         // Depends on your font
Style:     normal         // Depends on your font
```
When you will save, the `@font-face` code will be added automatically.  
* * *

Then, to use those fonts, we have 2 different ways.

### #1 - Custom class
We can add a custom class in `Pilo'Press > Styles > Tailwind`, in CSS field.  
Something like that:
```css
.font-roboto {
    font-family: "Roboto", sans-serif;
}

.font-awesome-font {
    font-family: "Awesome Font", sans-serif;
}
```
After re-building styles, we will be able to use those classes everywhere.

### #2 - Tailwind CSS configuration file
As explain in [Tailwind CSS Documentation](https://tailwindcss.com/docs/font-family/#font-families), you can define custom fonts and modify the default ones.  
Let's say we want to add our custom fonts without removing default ones, so we can write something like that:
```js
module.exports = {
    theme: {
        extend: {
            fontFamily: {
                roboto: ['Roboto', 'sans-serif'],
                'awesome-font': ['Awesome Font', 'sans-serif'],
            },
        },
    },
};
```
Tailwind CSS will generate the following classes: `font-roboto` and `font-homework`.

**CAUTION:** If your font name has a dash or something similar, you **have to** wrap it with quotes as in code example above.  
If you don't, compilation will fail.
