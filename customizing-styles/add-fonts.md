---
layout: default
title: Add fonts
parent: Customizing styles
nav_order: 1
---

## Add fonts
First step, we will go to `Pilo'Press > Styles > Fonts` and add a font.  
You have 2 choices : Google Font or Custom font.  

**Example: Google Font**

Let's say we want to add Google's Roboto Font.  
We have to fill the fields as following:  
```text
Name:            Roboto
URL:             https://fonts.googleapis.com/css2?family=Roboto&display=swap
Auto-enqueue:    true
```
**NB:** The `Auto-enqueue` option will automatically add the `<link>` tag if set to `true`.  
____

**Example: Custom font**

Let's say we want to add a font named _Homework_.  
_Be careful with your font formats, because of [browser compatibility](https://www.w3schools.com/css/css3_fonts.asp)._  

We have to fill the fields as following:  
```text
Name:      Homework
Files:     <Your files>
Weight:    normal         // Depends on your font
Style:     normal         // Depends on your font
```
When you will save, the `@font-face` code will be added automatically.  
____

Then, to use those fonts, we have 2 different ways.

### #1 - Custom class
We can add a custom class in `Pilo'Press > Styles > Tailwind`, in CSS field.  
Something like that:
```css
.font-roboto {
    font-family: "Roboto", sans-serif;
}

.font-homework {
    font-family: "Homework", sans-serif;
}
```
After re-building styles, we will be able to use those classes everywhere.

### #2 - Tailwind configuration file
As explain in [Tailwind Documentation](https://tailwindcss.com/docs/font-family/#font-families), you can define custom fonts and modify the default ones.  
Let's say we want to add our custom fonts without removing default ones, so we can write something like that:
```js
module.exports = {
    theme: {
        extend: {
            fontFamily: {
                roboto: ['Roboto', 'sans-serif'],
                homework: ['Homework', 'sans-serif'],
            },
        },
    },
};
```
Tailwind will generate the following classes: `font-roboto` and `font-homework`.
