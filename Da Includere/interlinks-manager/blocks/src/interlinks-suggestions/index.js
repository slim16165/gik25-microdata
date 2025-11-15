const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daim-interlinks-suggestions',
    {
      icon: false,
      render,
    },
);