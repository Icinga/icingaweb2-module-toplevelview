Custom Styling
==============

With a custom theme you can change style and colors of the Top Level View.

Here are a few examples.

## Blinking unhandled

So that unhandled problems are more visible.

```less
.container.icinga-module.module-toplevelview {
  .tlv-status-tile.unhandled {
    animation: blinker 1.5s linear infinite;
  }
}

@keyframes blinker {
  50% {
    opacity: 0.2;
  }
}
```

## Dark full screen

When you open the TLV in full screen mode, background will be dark.

```less
.fullscreen-layout {
  .container.icinga-module.module-toplevelview {
    color: #eee;
    a:visited {
      color: inherit;
    }
    background: #333;

    .controls {
      background: inherit;

      .active {
        color: #333;
      }
    }
  }
}
```
