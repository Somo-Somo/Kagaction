<template>
  <v-container
    class="d-flex flex-column py-6 my-md-2 px-md-16"
    style="max-width: 900px"
    fluid
  >
    <div class="d-flex justify-space-between" style="position: fixed" :style="getHypothesisBind.tabStyle">
      <v-tabs class="" color="black" center-active>
        <v-tabs-slider color="#80CBC4"></v-tabs-slider>
        <v-tab>Purpose</v-tab>
        <v-tab>Today's Goal</v-tab>
        <v-tab>Issue</v-tab>
        <v-tab>Finished</v-tab>
      </v-tabs>
      <v-icon class="hidden-sm-and-down" size="24">mdi-plus-circle</v-icon>
    </div>
    <!-- PC版 -->
    <div class="overflow-y-auto d-flex" :class="getHypothesisBind.cardClass" :style="getHypothesisBind.cardStyle">
      <Cards />
    </div>
    <AddCard />
  </v-container>
</template>

<script>
import Cards from "../components/Cards.vue";
import AddCard from "../components/AddCard.vue";

export default {
  components: {
    Cards,
    AddCard,
  },
  computed: {
    getHypothesisBind() {
      if (this.$vuetify.breakpoint.mdAndUp) {
        return {
          cardClass: null,
          cardStyle : {
            height: 'calc(100vh - 144px)',
            position: 'relative',
            top: '56px'
          },
          tabStyle: null
        };
      } else {
        return {
          cardClass: {
            "justify-content-center": true,
          },
          cardStyle : {
            height: 'calc(100vh - 224px)', 
            position: 'relative',
            top: '56px'
          },
          tabStyle : {
            width: '100vw',
            left: '-36px'
          }
        };
      }
    },
  },
};
</script>