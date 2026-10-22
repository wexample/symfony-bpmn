import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/bpmn_lane.json';

export default class BpmnLane extends AbstractApiEntity {
  static readonly entityName = 'bpmnLane';

  static retrieveEntitySchema() {
    return schema;
  }
}
